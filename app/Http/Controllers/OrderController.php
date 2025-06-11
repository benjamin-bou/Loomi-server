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
use Carbon\Carbon;

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
            'subscription.subscriptionType',
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

        // Validation initiale
        if (!$user || empty($items)) {
            return response()->json(['error' => 'Utilisateur non authentifié ou panier vide'], 400);
        }

        // Analyser les items du panier
        $itemsAnalysis = $this->analyzeCartItems($items);

        Log::info('OrderController@store', [
            'user_id' => $user->id,
            'items_count' => count($items),
            'payment_method' => $paymentMethod,
            'gift_card_id' => $giftCardId,
            'analysis' => $itemsAnalysis,
            'raw_items' => $items, // Ajout pour debug
        ]);

        // Vérifier les restrictions d'abonnement
        $subscriptionValidation = $this->validateSubscriptionPurchase($user, $items, $itemsAnalysis);
        if (!$subscriptionValidation['valid']) {
            return response()->json(['error' => $subscriptionValidation['message']], 400);
        }

        // Valider la carte cadeau utilisée si présente
        $usedGiftCard = $this->validateUsedGiftCard($itemsAnalysis['giftCardCode'], $giftCardId);
        if ($itemsAnalysis['hasGiftCardUsage'] && !$usedGiftCard) {
            return response()->json(['error' => 'Carte cadeau invalide ou déjà utilisée'], 400);
        }

        // Calculer le montant total à payer
        $total = $this->calculateOrderTotal($items, $itemsAnalysis);

        // Créer la commande
        $order = $this->createOrder($user, $total, $itemsAnalysis['hasBoxes']);

        // Traiter chaque type d'item
        $this->processOrderItems($order, $items, $usedGiftCard);

        // Gérer les paiements
        $this->handlePayments($order, $paymentMethod, $usedGiftCard, $total);

        // Finaliser le statut de la commande
        $this->finalizeOrderStatus($order, $itemsAnalysis);

        return response()->json([
            'success' => true,
            'order_id' => $order->id,
            'message' => 'Commande créée avec succès',
            'order' => $order->load(['boxOrders.box', 'subscription.subscriptionType', 'paymentMethods.paymentMethodType'])
        ], 201);
    }

    /**
     * Analyser les items du panier pour comprendre leur composition
     */
    private function analyzeCartItems(array $items): array
    {
        $analysis = [
            'hasBoxes' => false,
            'hasSubscriptions' => false,
            'hasGiftCards' => false,
            'hasGiftCardUsage' => false,
            'giftCardCode' => null,
            'paidItemsCount' => 0,
            'freeItemsCount' => 0,
        ];

        foreach ($items as $item) {
            $type = $item['type'] ?? null;

            switch ($type) {
                case 'box':
                    $analysis['hasBoxes'] = true;
                    if ($item['paidWithGiftCard'] ?? false) {
                        $analysis['freeItemsCount']++;
                        $analysis['hasGiftCardUsage'] = true;
                        $analysis['giftCardCode'] = $item['giftCardCode'] ?? $analysis['giftCardCode'];
                    } else {
                        $analysis['paidItemsCount']++;
                    }
                    break;

                case 'subscription':
                    $analysis['hasSubscriptions'] = true;
                    if ($item['paidWithGiftCard'] ?? false) {
                        $analysis['freeItemsCount']++;
                        $analysis['hasGiftCardUsage'] = true;
                        $analysis['giftCardCode'] = $item['giftCardCode'] ?? $analysis['giftCardCode'];
                    } else {
                        $analysis['paidItemsCount']++;
                    }
                    break;

                case 'giftcard':
                    $analysis['hasGiftCards'] = true;
                    $analysis['paidItemsCount']++;
                    break;

                case 'giftcard_usage':
                    $analysis['hasGiftCardUsage'] = true;
                    $analysis['giftCardCode'] = $item['giftCardCode'] ?? null;
                    break;
            }
        }

        return $analysis;
    }

    /**
     * Valider l'achat d'abonnement en fonction du statut actuel de l'utilisateur
     */
    private function validateSubscriptionPurchase($user, array $items, array $analysis): array
    {
        // Si pas d'abonnement dans le panier, pas de validation nécessaire
        if (!$analysis['hasSubscriptions']) {
            return ['valid' => true];
        }

        // Vérifier si l'utilisateur a déjà un abonnement actif
        $currentSubscription = $this->getCurrentActiveSubscription($user);

        foreach ($items as $item) {
            if ($item['type'] === 'subscription') {
                // Si l'utilisateur a déjà un abonnement actif
                if ($currentSubscription) {
                    // Si c'est une carte cadeau d'abonnement, on peut l'utiliser pour étendre l'abonnement
                    if ($item['paidWithGiftCard'] ?? false) {
                        // Vérifier que c'est bien une carte cadeau d'abonnement
                        $giftCardCode = $item['giftCardCode'] ?? null;
                        if ($giftCardCode) {
                            $giftCard = GiftCard::where('code', $giftCardCode)->first();
                            if ($giftCard && $giftCard->giftCardType) {
                                $isSubscriptionGiftCard = stripos($giftCard->giftCardType->name, 'abonnement') !== false
                                    || stripos($giftCard->giftCardType->name, 'subscription') !== false;

                                if ($isSubscriptionGiftCard) {
                                    continue; // C'est valide, on peut étendre l'abonnement
                                }
                            }
                        }
                        return [
                            'valid' => false,
                            'message' => 'Cette carte cadeau ne peut pas être utilisée pour étendre votre abonnement actuel.'
                        ];
                    } else {
                        // Tentative d'achat d'un nouvel abonnement alors qu'il y en a déjà un actif
                        return [
                            'valid' => false,
                            'message' => 'Vous avez déjà un abonnement actif. Vous ne pouvez pas acheter un nouvel abonnement.'
                        ];
                    }
                }
            }
        }

        return ['valid' => true];
    }

    /**
     * Récupérer l'abonnement actif actuel de l'utilisateur
     */
    private function getCurrentActiveSubscription($user): ?Subscription
    {
        $order = $user->orders()
            ->where('active', true)
            ->whereNotNull('subscription_id')
            ->latest()
            ->first();

        if (!$order || !$order->subscription) {
            return null;
        }

        $subscription = $order->subscription;

        // Vérifier si l'abonnement est vraiment actif
        if ($subscription->isActive()) {
            return $subscription;
        }

        return null;
    }

    /**
     * Valider la carte cadeau utilisée
     */
    private function validateUsedGiftCard(?string $giftCardCode, ?int $giftCardId): ?GiftCard
    {
        $usedGiftCard = null;

        if ($giftCardId) {
            $usedGiftCard = GiftCard::find($giftCardId);
        } elseif ($giftCardCode) {
            $usedGiftCard = GiftCard::where('code', $giftCardCode)->first();
        }

        if ($usedGiftCard) {
            if (
                $usedGiftCard->used_at ||
                ($usedGiftCard->expiration_date && $usedGiftCard->expiration_date < now())
            ) {
                return null;
            }
        }

        return $usedGiftCard;
    }

    /**
     * Calculer le montant total de la commande
     */
    private function calculateOrderTotal(array $items, array $analysis): float
    {
        $total = 0;

        foreach ($items as $item) {
            $type = $item['type'] ?? null;

            // Exclure les cartes cadeaux utilisées (type giftcard_usage)
            if ($type === 'giftcard_usage') {
                continue;
            }

            // Exclure les items payés avec carte cadeau
            if (($item['paidWithGiftCard'] ?? false)) {
                continue;
            }

            // Récupérer le prix depuis la base de données selon le type
            $price = 0;
            $quantity = $item['quantity'] ?? 1;

            switch ($type) {
                case 'box':
                    $box = \App\Models\Box::find($item['id']);
                    if ($box) {
                        $price = $box->base_price;
                    }
                    break;

                case 'subscription':
                    $subscriptionType = \App\Models\SubscriptionType::find($item['id']);
                    if ($subscriptionType) {
                        $price = $subscriptionType->price;
                    }
                    break;

                case 'giftcard':
                case 'gift_card':
                    $giftCardType = \App\Models\GiftCardType::find($item['id']);
                    if ($giftCardType) {
                        $price = $giftCardType->base_price;
                    }
                    break;

                default:
                    // Fallback pour les items avec prix direct
                    $price = $item['price'] ?? $item['base_price'] ?? 0;
                    break;
            }

            $total += $price * $quantity;
        }

        return $total;
    }

    /**
     * Créer la commande
     */
    private function createOrder($user, float $total, bool $hasBoxes): Order
    {
        $order = new Order();
        $order->user_id = $user->id;
        $order->order_number = uniqid('ORD-');
        $order->total_amount = $total;
        $order->status = 'pending';
        $order->active = true;

        if ($hasBoxes) {
            $order->delivery_date = $this->calculateDeliveryDate();
        }

        $order->save();

        return $order;
    }

    /**
     * Traiter tous les items de la commande
     */
    private function processOrderItems(Order $order, array $items, ?GiftCard $usedGiftCard): void
    {
        foreach ($items as $item) {
            $type = $item['type'] ?? null;

            switch ($type) {
                case 'box':
                    $this->processBoxItem($order, $item);
                    break;

                case 'subscription':
                    $this->processSubscriptionItem($order, $item);
                    break;

                case 'giftcard':
                case 'gift_card':
                    $this->processGiftCardItem($order, $item);
                    break;

                case 'giftcard_usage':
                    $this->processGiftCardUsage($order, $item, $usedGiftCard);
                    break;
            }
        }
    }

    /**
     * Traiter un item de type box
     */
    private function processBoxItem(Order $order, array $item): void
    {
        Log::info('Adding box to order', [
            'order_id' => $order->id,
            'box_id' => $item['id'],
            'box_name' => $item['name'] ?? 'Unknown',
            'quantity' => $item['quantity'] ?? 1,
            'paid_with_gift_card' => $item['paidWithGiftCard'] ?? false,
        ]);

        $order->boxes()->attach($item['id'], ['quantity' => $item['quantity'] ?? 1]);
    }

    /**
     * Traiter un item de type subscription
     */
    private function processSubscriptionItem(Order $order, array $item): void
    {
        $subscriptionType = SubscriptionType::find($item['id']);
        $user = Auth::user();

        if (!$subscriptionType) {
            Log::warning('Subscription type not found for order', [
                'subscription_type_id' => $item['id'],
                'order_id' => $order->id
            ]);
            return;
        }

        // Vérifier si l'utilisateur a déjà un abonnement actif
        $currentSubscription = $this->getCurrentActiveSubscription($user);

        // Si c'est une carte cadeau d'abonnement et qu'il y a déjà un abonnement actif
        if ($currentSubscription && ($item['paidWithGiftCard'] ?? false)) {
            // Étendre l'abonnement existant
            $this->extendExistingSubscription($currentSubscription, $subscriptionType, $order, $item);
        } else {
            // Créer un nouvel abonnement
            $this->createNewSubscription($subscriptionType, $order, $item);
        }
    }

    /**
     * Créer un nouvel abonnement
     */
    private function createNewSubscription(SubscriptionType $subscriptionType, Order $order, array $item): void
    {
        // Calculer les dates
        $startDate = now()->toDateString();
        $endDate = now()->addMonths($this->getSubscriptionDurationInMonths($subscriptionType->recurrence))->toDateString();

        // Créer l'abonnement
        $subscription = Subscription::create([
            'subscription_type_id' => $subscriptionType->id,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'status' => 'active',
            'auto_renew' => false,
        ]);

        // Associer à la commande
        $order->subscription_id = $subscription->id;
        $order->save();

        Log::info('New subscription created for order', [
            'subscription_id' => $subscription->id,
            'subscription_type_label' => $subscriptionType->label,
            'order_id' => $order->id,
            'paid_with_gift_card' => $item['paidWithGiftCard'] ?? false,
            'start_date' => $startDate,
            'end_date' => $endDate
        ]);
    }

    /**
     * Étendre un abonnement existant avec une carte cadeau
     */
    private function extendExistingSubscription(Subscription $currentSubscription, SubscriptionType $subscriptionType, Order $order, array $item): void
    {
        // Calculer la nouvelle date de fin en ajoutant les mois à partir de la date de fin actuelle
        $currentEndDate = \Carbon\Carbon::parse($currentSubscription->end_date);
        $additionalMonths = $this->getSubscriptionDurationInMonths($subscriptionType->recurrence);
        $newEndDate = $currentEndDate->addMonths($additionalMonths);

        // Mettre à jour l'abonnement existant
        $currentSubscription->end_date = $newEndDate->toDateString();
        $currentSubscription->save();

        // Associer cette commande à l'abonnement existant pour traçabilité
        $order->subscription_id = $currentSubscription->id;
        $order->save();

        Log::info('Subscription extended with gift card', [
            'subscription_id' => $currentSubscription->id,
            'subscription_type_label' => $subscriptionType->label,
            'order_id' => $order->id,
            'gift_card_code' => $item['giftCardCode'] ?? 'unknown',
            'additional_months' => $additionalMonths,
            'new_end_date' => $newEndDate->toDateString(),
            'old_end_date' => $currentEndDate->subMonths($additionalMonths)->toDateString()
        ]);
    }

    /**
     * Traiter un item de type giftcard (achat de carte cadeau)
     */
    private function processGiftCardItem(Order $order, array $item): void
    {
        $this->createGiftCardsForOrder($order, $item);
    }

    /**
     * Traiter l'utilisation d'une carte cadeau
     */
    private function processGiftCardUsage(Order $order, array $item, ?GiftCard $usedGiftCard): void
    {
        if (!$usedGiftCard) {
            Log::warning('No valid gift card found for usage', [
                'gift_card_code' => $item['giftCardCode'] ?? 'missing',
                'order_id' => $order->id
            ]);
            return;
        }

        // Marquer la carte comme utilisée
        $usedGiftCard->used_at = now();
        $usedGiftCard->save();

        Log::info('Gift card used in order', [
            'gift_card_id' => $usedGiftCard->id,
            'code' => $usedGiftCard->code,
            'order_id' => $order->id,
        ]);
    }

    /**
     * Gérer les paiements de la commande
     */
    private function handlePayments(Order $order, ?string $paymentMethod, ?GiftCard $usedGiftCard, float $total): void
    {
        // Paiement par carte cadeau
        if ($usedGiftCard) {
            $giftCardPaymentType = PaymentMethodType::where('name', 'Gift Card')->first();
            if ($giftCardPaymentType) {
                $order->paymentMethods()->create([
                    'payment_method_type_id' => $giftCardPaymentType->id,
                    'gift_card_id' => $usedGiftCard->id,
                    'amount' => 0, // Montant gratuit avec carte cadeau
                ]);
            }
        }

        // Paiement classique pour le montant restant
        if ($paymentMethod && $total > 0) {
            // Mapper les noms de méthodes de paiement du frontend vers la base de données
            $paymentMethodMapping = [
                'visa' => 'Credit Card',
                'cb' => 'Credit Card',
                'paypal' => 'PayPal',
                'apple_pay' => 'Apple Pay',
                'applepay' => 'Apple Pay',
                'google_pay' => 'Google Pay',
                'googlepay' => 'Google Pay',
                'samsung_pay' => 'Samsung Pay',
                'samsungpay' => 'Samsung Pay',
            ];

            $mappedPaymentMethod = $paymentMethodMapping[$paymentMethod] ?? $paymentMethod;

            $paymentType = PaymentMethodType::where('name', $mappedPaymentMethod)->first();
            if ($paymentType) {
                $order->paymentMethods()->create([
                    'payment_method_type_id' => $paymentType->id,
                    'amount' => $total,
                ]);
            } else {
                Log::warning('Payment method type not found', [
                    'frontend_method' => $paymentMethod,
                    'mapped_method' => $mappedPaymentMethod,
                    'order_id' => $order->id
                ]);
            }
        }
    }

    /**
     * Finaliser le statut de la commande selon sa composition
     */
    private function finalizeOrderStatus(Order $order, array $analysis): void
    {
        // Marquer comme complétée dans ces cas :
        // 1. Commande entièrement gratuite (payée avec carte cadeau)
        // 2. Commande uniquement de cartes cadeaux (sans boîtes ni abonnements)
        // Note: Les abonnements restent en "pending" pour permettre l'activation manuelle
        if (($order->total_amount == 0 && $analysis['freeItemsCount'] > 0) ||
            ($analysis['hasGiftCards'] && !$analysis['hasBoxes'] && !$analysis['hasSubscriptions'])
        ) {
            $order->status = 'completed';
            $order->save();

            Log::info('Order status finalized as completed', [
                'order_id' => $order->id,
                'reason' => $analysis['hasGiftCards'] && !$analysis['hasBoxes'] && !$analysis['hasSubscriptions']
                    ? 'gift_cards_only'
                    : 'free_with_gift_card',
                'analysis' => $analysis
            ]);
        }
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

    /**
     * Get available payment methods with frontend mappings
     *
     * @return \Illuminate\Http\Response
     */
    public function getPaymentMethods()
    {
        try {
            // Récupérer tous les types de méthodes de paiement sauf Gift Card (utilisée uniquement en interne)
            $paymentMethodTypes = PaymentMethodType::where('name', '!=', 'Gift Card')->get();

            // Mapping des noms de la DB vers le frontend
            $dbToFrontendMapping = [
                'Credit Card' => ['key' => 'cb', 'label' => 'Carte bancaire'],
                'PayPal' => ['key' => 'paypal', 'label' => 'PayPal'],
                'Apple Pay' => ['key' => 'applepay', 'label' => 'Apple Pay'],
                'Google Pay' => ['key' => 'googlepay', 'label' => 'Google Pay'],
                'Samsung Pay' => ['key' => 'samsungpay', 'label' => 'Samsung Pay'],
            ];

            $paymentMethods = [];
            foreach ($paymentMethodTypes as $type) {
                if (isset($dbToFrontendMapping[$type->name])) {
                    $paymentMethods[] = $dbToFrontendMapping[$type->name];
                }
            }

            // Ajouter Visa comme alias de Credit Card s'il n'existe pas déjà
            $hasVisa = collect($paymentMethods)->contains('key', 'visa');
            $hasCreditCard = collect($paymentMethods)->contains('key', 'cb');

            if (!$hasVisa && $hasCreditCard) {
                array_unshift($paymentMethods, ['key' => 'visa', 'label' => 'Visa']);
            }

            return response()->json([
                'success' => true,
                'payment_methods' => $paymentMethods
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching payment methods: ' . $e->getMessage());

            // Fallback vers les méthodes par défaut en cas d'erreur
            return response()->json([
                'success' => true,
                'payment_methods' => [
                    ['key' => 'visa', 'label' => 'Visa'],
                    ['key' => 'cb', 'label' => 'Carte bancaire'],
                    ['key' => 'paypal', 'label' => 'PayPal'],
                    ['key' => 'applepay', 'label' => 'Apple Pay'],
                ]
            ]);
        }
    }
}
