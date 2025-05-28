<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\PaymentMethodType;
use App\Models\GiftCard;
use App\Models\GiftCardType;
use App\Models\Subscription;
use App\Models\SubscriptionType;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        $orders = $user->orders()->with([
            'paymentMethods.paymentMethodType',
            'boxOrders.box',
            'createdGiftCards.giftCardType',
        ])->orderBy('created_at', 'desc')->get();
        return response()->json([
            'user' => $user,
            'orders' => $orders
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        // Logic to show form for creating a new order
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $items = $request->input('items', []);
        $paymentMethod = $request->input('payment_method');
        $giftCardId = $request->input('gift_card_id');

        Log::info('OrderController@store', [
            'user_id' => $user ? $user->id : null,
            'items_count' => count($items),
            'payment_method' => $paymentMethod,
            'gift_card_id' => $giftCardId,
            'has_gift_card_in_items' => collect($items)->contains('type', 'giftcard_usage'),
        ]);

        if (!$user || empty($items)) {
            return response()->json(['error' => 'Utilisateur non authentifié ou panier vide'], 400);
        }

        // Vérifier s'il y a une carte cadeau utilisée dans les items du panier
        $giftCardUsedInCart = null;
        foreach ($items as $item) {
            if (($item['type'] ?? null) === 'giftcard_usage' && !empty($item['giftCardCode'])) {
                $giftCardUsedInCart = GiftCard::where('code', $item['giftCardCode'])->first();
                break;
            }
        }

        // Si paiement par carte cadeau, vérifier qu'elle est valide
        $usedGiftCard = null;
        if ($giftCardId) {
            $usedGiftCard = GiftCard::find($giftCardId);
        } elseif ($giftCardUsedInCart) {
            $usedGiftCard = $giftCardUsedInCart;
        }

        if ($usedGiftCard) {
            if (
                $usedGiftCard->used_at ||
                ($usedGiftCard->expiration_date && $usedGiftCard->expiration_date < now())
            ) {
                return response()->json(['error' => 'Carte cadeau invalide ou déjà utilisée'], 400);
            }
        }

        // Calcul du total en excluant les items gratuits (payés avec carte cadeau)
        $total = 0;
        foreach ($items as $item) {
            // Exclure les cartes cadeaux utilisées et les boxes payées avec carte cadeau
            if (($item['type'] ?? null) === 'giftcard_usage') {
                continue;
            }
            if (($item['type'] ?? null) === 'box' && ($item['paidWithGiftCard'] ?? false)) {
                continue;
            }
            $total += ($item['price'] ?? $item['base_price'] ?? 0) * ($item['quantity'] ?? 1);
        }

        // Création de la commande
        $order = new Order();
        $order->user_id = $user->id;
        $order->order_number = uniqid('ORD-');
        $order->total_amount = $total;
        $order->status = 'pending';
        $order->active = true;

        // Calculer la date de livraison si la commande contient des boîtes
        $hasBoxes = false;
        foreach ($items as $item) {
            if (($item['type'] ?? null) === 'box') {
                $hasBoxes = true;
                break;
            }
        }

        if ($hasBoxes) {
            $order->delivery_date = $this->calculateDeliveryDate();
        }

        $order->save();

        // Ajout des boxes à la commande (table pivot box_orders)
        foreach ($items as $item) {
            if (($item['type'] ?? null) === 'box') {
                Log::info('Adding box to order', [
                    'order_id' => $order->id,
                    'box_id' => $item['id'],
                    'box_name' => $item['name'] ?? 'Unknown',
                    'quantity' => $item['quantity'] ?? 1,
                    'user_id' => $user->id
                ]);
                $order->boxes()->attach($item['id'], ['quantity' => $item['quantity'] ?? 1]);
            }

            // Gestion des gift card types : création automatique de gift card
            if (($item['type'] ?? null) === 'giftcard') {
                $this->createGiftCardsForOrder($order, $item);
            }

            // Gestion de l'utilisation de carte cadeau
            if (($item['type'] ?? null) === 'giftcard_usage') {
                // Vérifier que la carte cadeau existe et est valide
                $giftCard = GiftCard::where('code', $item['giftCardCode'] ?? '')->first();
                if ($giftCard && !$giftCard->used_at) {
                    // Marquer la carte comme utilisée
                    $giftCard->used_at = now();
                    $giftCard->save();

                    Log::info('GiftCard used via cart', [
                        'gift_card_id' => $giftCard->id,
                        'code' => $giftCard->code,
                        'order_id' => $order->id,
                        'user_id' => $user->id
                    ]);
                } else {
                    Log::warning('Invalid gift card in cart', [
                        'gift_card_code' => $item['giftCardCode'] ?? 'missing',
                        'order_id' => $order->id
                    ]);
                }
            }

            // Gestion des abonnements
            if (($item['type'] ?? null) === 'subscription') {
                $subscriptionType = SubscriptionType::find($item['id']);

                if ($subscriptionType) {
                    // Calculer les dates de début et fin
                    $startDate = now()->toDateString();
                    $endDate = now()->addMonths($this->getSubscriptionDurationInMonths($subscriptionType->recurrence))->toDateString();

                    // Créer l'abonnement utilisateur
                    $subscription = Subscription::create([
                        'subscription_type_id' => $subscriptionType->id,
                        'start_date' => $startDate,
                        'end_date' => $endDate,
                        'status' => 'active',
                        'auto_renew' => false,
                    ]);

                    // Associer l'abonnement à la commande
                    $order->subscription_id = $subscription->id;
                    $order->status = 'completed';
                    $order->save();

                    Log::info('User subscription created', [
                        'subscription_id' => $subscription->id,
                        'subscription_type_label' => $subscriptionType->label,
                        'order_id' => $order->id,
                        'user_id' => $user->id,
                        'start_date' => $startDate,
                        'end_date' => $endDate
                    ]);
                } else {
                    Log::warning('Subscription type not found for order', [
                        'subscription_type_id' => $item['id'],
                        'order_id' => $order->id
                    ]);
                }
            }

            // Ici on peut gérer d'autres types si nécessaire
        }

        // Marquer la carte cadeau comme utilisée si paiement par carte cadeau
        if ($usedGiftCard) {
            $usedGiftCard->used_at = now();
            $usedGiftCard->save();

            Log::info('GiftCard used for payment', [
                'gift_card_id' => $usedGiftCard->id,
                'code' => $usedGiftCard->code,
                'order_id' => $order->id,
                'user_id' => $user->id
            ]);
        }

        // Enregistrer les moyens de paiement
        $hasPayableItems = $total > 0; // Y a-t-il des items à payer ?

        if ($usedGiftCard) {
            // Enregistrer le paiement par carte cadeau
            $giftCardPaymentType = PaymentMethodType::where('name', 'Gift Card')->first();
            if ($giftCardPaymentType) {
                $order->paymentMethods()->create([
                    'payment_method_type_id' => $giftCardPaymentType->id,
                    'gift_card_id' => $usedGiftCard->id,
                    'amount' => 0, // Le montant est gratuit avec carte cadeau
                ]);
            }
        }

        if ($paymentMethod && $hasPayableItems) {
            // Enregistrer le paiement classique pour les items payants
            $paymentType = PaymentMethodType::where('name', $paymentMethod)->first();
            if ($paymentType) {
                $order->paymentMethods()->create([
                    'payment_method_type_id' => $paymentType->id,
                    'amount' => $total,
                ]);
            }
        }

        return response()->json(['success' => true, 'order_id' => $order->id]);
    }

    /**
     * Créer automatiquement une gift card lorsqu'un gift card type est commandé
     *
     * @param  \App\Models\Order  $order
     * @param  array  $item
     * @return void
     */
    private function createGiftCardsForOrder(Order $order, array $item)
    {
        try {
            // Vérifier si le gift card type existe
            $giftCardType = GiftCardType::find($item['id']);
            if (!$giftCardType) {
                Log::warning('GiftCardType not found for creation', [
                    'giftcard_type_id' => $item['id'],
                    'order_id' => $order->id
                ]);
                return;
            }

            $quantity = $item['quantity'] ?? 1;

            // Créer une gift card pour chaque quantité commandée
            for ($i = 0; $i < $quantity; $i++) {
                $giftCard = new GiftCard();
                $giftCard->code = $this->generateUniqueGiftCardCode();
                $giftCard->gift_card_type_id = $giftCardType->id;
                $giftCard->order_id = $order->id;
                $giftCard->expiration_date = now()->addYear(); // Expire dans 1 an
                $giftCard->used_at = null;
                $giftCard->save();

                Log::info('GiftCard created automatically', [
                    'gift_card_id' => $giftCard->id,
                    'code' => $giftCard->code,
                    'gift_card_type' => $giftCardType->name,
                    'order_id' => $order->id,
                    'user_id' => $order->user_id
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Error creating gift card for order', [
                'error' => $e->getMessage(),
                'order_id' => $order->id,
                'giftcard_type_id' => $item['id'] ?? null
            ]);
        }
    }

    /**
     * Générer un code unique pour la gift card
     *
     * @return string
     */
    private function generateUniqueGiftCardCode()
    {
        do {
            // Format: GIFT-XXXX-XXXX (où X = lettre ou chiffre)
            $code = 'GIFT-' . strtoupper(Str::random(4)) . '-' . strtoupper(Str::random(4));
        } while (GiftCard::where('code', $code)->exists());

        return $code;
    }

    /**
     * Calculer la durée d'un abonnement en mois selon sa récurrence
     *
     * @param  string  $recurrence
     * @return int
     */
    private function getSubscriptionDurationInMonths($recurrence)
    {
        switch ($recurrence) {
            case 'monthly':
                return 1;
            case 'quarterly':
                return 3;
            case 'semi-annual':
                return 6;
            case 'annual':
                return 12;
            default:
                return 1; // Par défaut, 1 mois
        }
    }

    /**
     * Calculer la date de livraison (7 jours ouvrés après la commande)
     *
     * @return string
     */
    private function calculateDeliveryDate()
    {
        $date = now();
        $daysAdded = 0;

        while ($daysAdded < 7) {
            $date->addDay();
            // Ignorer les weekends (samedi = 6, dimanche = 0)
            if ($date->dayOfWeek !== 0 && $date->dayOfWeek !== 6) {
                $daysAdded++;
            }
        }

        return $date->toDateString();
    }
}
