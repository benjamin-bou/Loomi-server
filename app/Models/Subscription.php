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
}
