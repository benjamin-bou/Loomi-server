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
