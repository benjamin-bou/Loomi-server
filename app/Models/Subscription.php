<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Subscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'subscription_type_id',
        'start_date',
        'end_date',
        'status',
        'auto_renew',
    ];

    public function subscriptionType()
    {
        return $this->belongsTo(SubscriptionType::class);
    }

    public function type()
    {
        return $this->belongsTo(SubscriptionType::class, 'subscription_type_id');
    }

    public function order()
    {
        return $this->hasOne(Order::class);
    }

    public function deliveries()
    {
        return $this->hasMany(SubscriptionDelivery::class);
    }

    // Méthodes utiles pour la gestion des abonnements
    public function isActive()
    {
        return $this->status === 'active' && $this->end_date >= now()->toDateString();
    }

    public function isExpired()
    {
        return $this->end_date < now()->toDateString();
    }

    public function daysRemaining()
    {
        return now()->diffInDays($this->end_date, false);
    }

    public function monthsRemaining()
    {
        return now()->diffInMonths($this->end_date, false);
    }

    /**
     * Calculer le nombre de mois offerts par les cartes cadeaux
     */
    public function getGiftCardMonthsAttribute()
    {
        $giftCardOrders = $this->order->user->orders()
            ->where('subscription_id', $this->id)
            ->whereHas('paymentMethods', function ($query) {
                $query->whereHas('paymentMethodType', function ($subQuery) {
                    $subQuery->where('name', 'Gift Card');
                });
            })
            ->with(['paymentMethods.giftCard.giftCardType'])
            ->get();

        $totalMonths = 0;

        foreach ($giftCardOrders as $order) {
            foreach ($order->paymentMethods as $payment) {
                if ($payment->giftCard && $payment->giftCard->giftCardType) {
                    // Extraire le nombre de mois du nom de la carte cadeau
                    preg_match('/(\d+)\s*mois/i', $payment->giftCard->giftCardType->name, $matches);
                    $months = isset($matches[1]) ? (int)$matches[1] : 1;
                    $totalMonths += $months;
                }
            }
        }

        return $totalMonths;
    }
}
