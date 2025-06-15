<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use App\Models\SubscriptionType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class SubscriptionController extends Controller
{
    /**
     * @OA\Get(
     *     path="/subscriptions",
     *     tags={"Subscriptions"},
     *     summary="Get all subscription types",
     *     description="Retrieve all available subscription types",
     *     @OA\Response(
     *         response=200,
     *         description="List of subscription types",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/SubscriptionType")
     *         )
     *     )
     * )
     */
    public function index()
    {
        return response()->json(SubscriptionType::all());
    }

    /**
     * @OA\Get(
     *     path="/subscriptions/{id}",
     *     tags={"Subscriptions"},
     *     summary="Get subscription type details",
     *     description="Retrieve detailed information about a specific subscription type",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Subscription type ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Subscription type details",
     *         @OA\JsonContent(ref="#/components/schemas/SubscriptionType")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Subscription type not found",
     *         @OA\JsonContent(ref="#/components/schemas/ApiError")
     *     )
     * )
     */
    public function show($id)
    {
        $subscriptionType = SubscriptionType::findOrFail($id);
        return response()->json($subscriptionType);
    }

    /**
     * @OA\Get(
     *     path="/my-subscription",
     *     tags={"Subscriptions"},
     *     summary="Get current user subscription",
     *     description="Retrieve current user's active subscription with gift card extensions",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Current subscription details",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="subscription", ref="#/components/schemas/Subscription"),
     *             @OA\Property(property="user", ref="#/components/schemas/User"),
     *             @OA\Property(
     *                 property="gift_card_extensions",
     *                 type="object",
     *                 @OA\Property(property="total_months", type="integer", example=3),
     *                 @OA\Property(
     *                     property="details",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="code", type="string", example="GIFT123"),
     *                         @OA\Property(property="type_name", type="string", example="3 mois"),
     *                         @OA\Property(property="months", type="integer", example=3),
     *                         @OA\Property(property="used_at", type="string", format="date-time")
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(ref="#/components/schemas/ApiError")
     *     )
     * )
     */
    public function current()
    {
        $user = Auth::user();
        $order = $user->orders()->where('active', true)->whereNotNull('subscription_id')->latest()->first();
        $subscription = $order ? $order->subscription()->with(['type'])->first() : null;

        $result = [
            'subscription' => $subscription,
            'user' => $user
        ];

        // Si un abonnement existe, calculer les informations sur les extensions par cartes cadeaux
        if ($subscription) {
            $result['gift_card_extensions'] = $this->calculateGiftCardExtensions($user, $subscription);
        }

        return response()->json($result);
    }

    /**
     * @OA\Post(
     *     path="/cancel-subscription",
     *     tags={"Subscriptions"},
     *     summary="Cancel user subscription",
     *     description="Cancel the current user's active subscription",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Subscription cancelled successfully",
     *         @OA\JsonContent(ref="#/components/schemas/ApiResponse")
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(ref="#/components/schemas/ApiError")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="No active subscription found",
     *         @OA\JsonContent(ref="#/components/schemas/ApiError")
     *     )
     * )
     */
    public function cancel()
    {
        $user = Auth::user();
        $order = $user->orders()->where('active', true)->whereNotNull('subscription_id')->latest()->first();

        if (!$order || !$order->subscription) {
            return response()->json(['error' => 'Aucun abonnement actif trouvé'], 404);
        }

        $subscription = $order->subscription;

        // Vérifier si l'abonnement peut être annulé
        if ($subscription->status === 'cancelled') {
            return response()->json(['error' => 'Cet abonnement est déjà annulé'], 400);
        }

        if ($subscription->status === 'expired') {
            return response()->json(['error' => 'Cet abonnement a déjà expiré'], 400);
        }

        // Annuler l'abonnement
        $subscription->update([
            'status' => 'cancelled',
            'auto_renew' => false
        ]);

        // Désactiver la commande associée
        $order->update([
            'active' => false
        ]);

        return response()->json([
            'message' => 'Abonnement annulé avec succès',
            'subscription' => $subscription->fresh()->load('type')
        ]);
    }

    /**
     * Calculer les extensions d'abonnement via cartes cadeaux
     */
    private function calculateGiftCardExtensions($user, $subscription)
    {
        // Récupérer toutes les commandes liées à cet abonnement qui ont utilisé des cartes cadeaux
        $giftCardOrders = $user->orders()
            ->where('subscription_id', $subscription->id)
            ->whereHas('paymentMethods', function ($query) {
                $query->whereHas('paymentMethodType', function ($subQuery) {
                    $subQuery->where('name', 'Gift Card');
                });
            })
            ->with(['paymentMethods.giftCard.giftCardType'])
            ->get();

        $totalGiftCardMonths = 0;
        $giftCardDetails = [];

        foreach ($giftCardOrders as $order) {
            foreach ($order->paymentMethods as $payment) {
                if ($payment->giftCard && $payment->giftCard->giftCardType) {
                    $giftCardType = $payment->giftCard->giftCardType;

                    // Extraire le nombre de mois de la carte cadeau (basé sur le nom)
                    preg_match('/(\d+)\s*mois/i', $giftCardType->name, $matches);
                    $months = isset($matches[1]) ? (int)$matches[1] : 1;

                    $totalGiftCardMonths += $months;

                    $giftCardDetails[] = [
                        'code' => $payment->giftCard->code,
                        'type_name' => $giftCardType->name,
                        'months' => $months,
                        'used_at' => $payment->giftCard->used_at,
                        'order_date' => $order->created_at
                    ];
                }
            }
        }

        return [
            'total_months_offered' => $totalGiftCardMonths,
            'details' => $giftCardDetails
        ];
    }
}
