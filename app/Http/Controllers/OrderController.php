<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\PaymentMethodType;
use App\Models\GiftCard;
use App\Models\GiftCardType;
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
        $data = $request->all();

        $items = [];
        $paymentMethod = null;
        $giftCardId = null;
        if (!empty($data)) {
            // $body = json_decode($data['body'], true);
            $items = $data['items'] ?? [];
            $paymentMethod = $data['payment_method'] ?? null;
            $giftCardId = $data['gift_card_id'] ?? null;
        }

        Log::info('OrderController@store', [
            'user_id' => $user ? $user->id : null,
            'items' => $items,
            'payment_method' => $paymentMethod,
            'gift_card_id' => $giftCardId,
            'data' => $data,
        ]);

        if (!$user || empty($items)) {
            return response()->json(['error' => 'Utilisateur non authentifié ou panier vide'], 400);
        }

        // Si paiement par carte cadeau, vérifier qu'elle est valide
        $usedGiftCard = null;
        if ($giftCardId) {
            $usedGiftCard = GiftCard::find($giftCardId);
            if (
                !$usedGiftCard || $usedGiftCard->used_at ||
                ($usedGiftCard->expiration_date && $usedGiftCard->expiration_date < now())
            ) {
                return response()->json(['error' => 'Carte cadeau invalide ou déjà utilisée'], 400);
            }
        }

        // Calcul du total
        $total = 0;
        foreach ($items as $item) {
            $total += ($item['price'] ?? $item['base_price'] ?? 0) * ($item['quantity'] ?? 1);
        }

        // Création de la commande
        $order = new Order();
        $order->user_id = $user->id;
        $order->order_number = uniqid('ORD-');
        $order->total_amount = $total;
        $order->status = 'pending';
        $order->active = true;
        $order->save();

        // Ajout des boxes à la commande (table pivot box_orders)
        foreach ($items as $item) {
            if (($item['type'] ?? null) === 'box') {
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

            // Ici on peut gérer d'autres types (subscription, etc.)
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

        // (Optionnel) Enregistrer le moyen de paiement choisi
        if ($paymentMethod && !$usedGiftCard) {
            // On suppose que PaymentMethodType contient les types (visa, cb, etc.)
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
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        // Logic to display a specific order by ID
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        // Logic to show form for editing an existing order
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        // Logic to update an existing order
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

            // On peut décider de ne pas faire échouer la commande si la création de gift card échoue
            // ou lever une exception selon les besoins métier
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
}
