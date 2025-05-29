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
     * Récupère toutes les livraisons d'un utilisateur (commandes + abonnements)
     */
    public function getUserDeliveries(Request $request)
    {
        $user = Auth::user();
        $deliveries = collect();        // Récupérer les boîtes achetées directement via les commandes
        $orders = Order::where('user_id', $user->id)
            ->with(['boxOrders.box'])
            ->get();
        foreach ($orders as $order) {
            foreach ($order->boxOrders as $boxOrder) {
                if ($boxOrder->box) {
                    $isDelivered = in_array($this->getOrderStatus($order), ['delivered', 'completed']);
                    $canReview = $isDelivered && !$this->hasUserReviewedBox($user->id, $boxOrder->box->id);

                    $deliveries->push([
                        'id' => 'order_' . $order->id . '_' . $boxOrder->id,
                        'delivery_type' => 'order',
                        'box_id' => $boxOrder->box->id,
                        'box_name' => $boxOrder->box->name,
                        'order_date' => $order->created_at,
                        'delivery_date' => $order->created_at, // Pour les commandes, on utilise la date de commande
                        'status' => $this->getOrderStatus($order),
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
     * Détermine le statut d'une commande
     */
    private function getOrderStatus($order)
    {
        // Logique simple pour déterminer le statut
        // Vous pouvez l'adapter selon votre logique métier
        if ($order->tracking_number) {
            return 'shipped';
        }

        if ($order->status === 'completed') {
            return 'delivered';
        }

        if ($order->status === 'cancelled') {
            return 'cancelled';
        }

        return 'pending';
    }

    /**
     * Ajouter une livraison d'abonnement (appelé automatiquement lors de l'envoi d'une boîte)
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
     * Marquer une livraison d'abonnement comme livrée
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
     * Vérifier si un utilisateur a déjà laissé un avis pour une boîte
     */
    private function hasUserReviewedBox($userId, $boxId)
    {
        return Review::where('user_id', $userId)
            ->where('box_id', $boxId)
            ->exists();
    }
}
