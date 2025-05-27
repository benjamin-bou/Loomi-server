<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use App\Models\SubscriptionType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class SubscriptionController extends Controller
{
    // Liste tous les types d'abonnement
    public function index()
    {
        return response()->json(SubscriptionType::all());
    }

    // Détail d'un type d'abonnement
    public function show($id)
    {
        $subscriptionType = SubscriptionType::findOrFail($id);
        return response()->json($subscriptionType);
    }

    // Abonnement en cours de l'utilisateur connecté
    public function current()
    {
        $user = Auth::user();
        $order = $user->orders()->where('active', true)->whereNotNull('subscription_id')->latest()->first();
        $subscription = $order ? $order->subscription()->with(['type'])->first() : null;

        return response()->json([
            'subscription' => $subscription,
            'user' => $user
        ]);
    }

    // Annuler l'abonnement en cours
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

        return response()->json([
            'message' => 'Abonnement annulé avec succès',
            'subscription' => $subscription->fresh()->load('type')
        ]);
    }
}
