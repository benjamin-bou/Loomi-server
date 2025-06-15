<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\SubscriptionDelivery;
use App\Models\Subscription;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DeliveryController extends Controller
{
    /**
     * @OA\Get(
     *     path="/profile/deliveries",
     *     tags={"Deliveries"},
     *     summary="Get user deliveries",
     *     description="Retrieve all deliveries for the authenticated user (orders and subscription deliveries)",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="User deliveries retrieved successfully",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="id", type="string", example="order_1_1"),
     *                 @OA\Property(property="delivery_type", type="string", enum={"order", "subscription"}, example="order"),
     *                 @OA\Property(property="box_id", type="integer", example=1),
     *                 @OA\Property(property="box_name", type="string", example="Beauty Box Premium"),
     *                 @OA\Property(property="order_number", type="string", example="ORD-20241201-001"),
     *                 @OA\Property(property="quantity", type="integer", example=1),
     *                 @OA\Property(property="order_date", type="string", format="date-time"),
     *                 @OA\Property(property="delivery_date", type="string", format="date-time"),
     *                 @OA\Property(property="status", type="string", enum={"pending", "shipped", "delivered", "completed"}, example="delivered"),
     *                 @OA\Property(property="tracking_number", type="string", nullable=true, example="TR123456789"),
     *                 @OA\Property(property="delivery_address", type="string", nullable=true),
     *                 @OA\Property(property="can_review", type="boolean", example=true),
     *                 @OA\Property(property="is_delivered", type="boolean", example=true)
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
    public function getUserDeliveries(Request $request)
    {
        $user = Auth::user();
        $deliveries = collect();        // Récupérer les boîtes achetées directement via les commandes
        $orders = Order::where('user_id', $user->id)
            ->with(['boxOrders.box'])
            ->get();
        foreach ($orders as $order) {
            // Only include orders that are delivered or completed
            $orderStatus = $this->getOrderStatus($order);
            if (!in_array($orderStatus, ['delivered', 'completed'])) {
                continue;
            }

            foreach ($order->boxOrders as $boxOrder) {
                if ($boxOrder->box) {
                    $isDelivered = in_array($orderStatus, ['delivered', 'completed']);
                    $canReview = $isDelivered && !$this->hasUserReviewedBox($user->id, $boxOrder->box->id);

                    $deliveries->push([
                        'id' => 'order_' . $order->id . '_' . $boxOrder->id,
                        'delivery_type' => 'order',
                        'box_id' => $boxOrder->box->id,
                        'box_name' => $boxOrder->box->name,
                        'order_number' => $order->order_number,
                        'quantity' => $boxOrder->quantity,
                        'order_date' => $order->created_at,
                        'delivery_date' => $order->delivery_date ?? $order->created_at,
                        'status' => $orderStatus,
                        'tracking_number' => $order->tracking_number ?? null,
                        'delivery_address' => $order->delivery_address ?? null,
                        'can_review' => $canReview,
                        'is_delivered' => $isDelivered,
                    ]);
                }
            }
        } // Récupérer les boîtes reçues via les abonnements
        $subscriptionDeliveries = SubscriptionDelivery::whereHas('subscription.order', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })
            ->with(['box', 'subscription'])
            ->get();
        foreach ($subscriptionDeliveries as $delivery) {
            $canReview = !$this->hasUserReviewedBox($user->id, $delivery->box->id);

            $deliveries->push([
                'id' => 'subscription_' . $delivery->id,
                'delivery_type' => 'subscription',
                'box_id' => $delivery->box->id,
                'box_name' => $delivery->box->name,
                'subscription_name' => $delivery->subscription->name,
                'delivery_date' => $delivery->delivered_at,
                'status' => 'delivered', // Les livraisons d'abonnement sont toujours livrées
                'tracking_number' => null, // Pas de suivi pour les abonnements pour l'instant
                'delivery_address' => null, // Utilise l'adresse par défaut de l'utilisateur
                'can_review' => $canReview,
                'is_delivered' => true,
            ]);
        }

        // Trier par date de livraison décroissante
        $sortedDeliveries = $deliveries->sortByDesc(function ($delivery) {
            return $delivery['delivery_date'];
        })->values();

        return response()->json($sortedDeliveries);
    }

    /**
     * @OA\Post(
     *     path="/subscription-deliveries",
     *     tags={"Deliveries"},
     *     summary="Add subscription delivery",
     *     description="Add a new delivery for a subscription (admin only)",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             required={"subscription_id", "box_id", "delivery_date"},
     *             @OA\Property(property="subscription_id", type="integer", example=1),
     *             @OA\Property(property="box_id", type="integer", example=1),
     *             @OA\Property(property="delivery_date", type="string", format="date", example="2024-02-01"),
     *             @OA\Property(property="tracking_number", type="string", example="TR123456789")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Subscription delivery added successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Livraison d'abonnement ajoutée avec succès"),
     *             @OA\Property(property="delivery", ref="#/components/schemas/SubscriptionDelivery")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(ref="#/components/schemas/ApiError")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(ref="#/components/schemas/ValidationError")
     *     )
     * )
     */
    public function addSubscriptionDelivery(Request $request)
    {
        $request->validate([
            'subscription_id' => 'required|exists:subscriptions,id',
            'box_id' => 'required|exists:boxes,id',
            'delivered_at' => 'nullable|date',
        ]);

        $delivery = SubscriptionDelivery::create([
            'subscription_id' => $request->subscription_id,
            'box_id' => $request->box_id,
            'delivered_at' => $request->delivered_at ?? now(),
        ]);

        return response()->json([
            'message' => 'Livraison d\'abonnement ajoutée avec succès',
            'delivery' => $delivery->load(['box', 'subscription'])
        ]);
    }

    /**
     * @OA\Patch(
     *     path="/subscription-deliveries/{id}/delivered",
     *     tags={"Deliveries"},
     *     summary="Mark delivery as delivered",
     *     description="Mark a subscription delivery as delivered (admin only)",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Subscription delivery ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Delivery marked as delivered successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Livraison marquée comme livrée avec succès"),
     *             @OA\Property(property="delivery", ref="#/components/schemas/SubscriptionDelivery")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(ref="#/components/schemas/ApiError")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Delivery not found",
     *         @OA\JsonContent(ref="#/components/schemas/ApiError")
     *     )
     * )
     */
    public function markAsDelivered(Request $request, $id)
    {
        $delivery = SubscriptionDelivery::findOrFail($id);
        // Vérifier que l'utilisateur peut modifier cette livraison
        $user = Auth::user();
        if ($delivery->subscription->order->user_id !== $user->id && $user->role !== 'admin') {
            return response()->json(['error' => 'Non autorisé'], 403);
        }

        $delivery->update([
            'delivered_at' => now()
        ]);
        return response()->json([
            'message' => 'Livraison marquée comme livrée',
            'delivery' => $delivery->load(['box', 'subscription'])
        ]);
    }

    /**
     * Détermine le statut d'une commande
     */
    private function getOrderStatus($order)
    {
        // Logique simple pour déterminer le statut
        // Vous pouvez l'adapter selon votre logique métier
        if ($order->tracking_number) {
            return 'shipped';
        }

        if (in_array($order->status, ['completed', 'delivered'])) {
            return 'delivered';
        }

        if ($order->status === 'cancelled') {
            return 'cancelled';
        }

        return 'pending';
    }

    /**
     * Vérifier si un utilisateur a déjà laissé un avis pour une boîte
     */
    private function hasUserReviewedBox($userId, $boxId)
    {
        return Review::where('user_id', $userId)
            ->where('box_id', $boxId)
            ->exists();
    }
}
