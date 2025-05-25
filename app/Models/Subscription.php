<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Subscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'duration',
        'base_price',
        'active',
        'renouvellement',
        'subscription_type_id',
    ];

    public function type()
    {
        return $this->belongsTo(SubscriptionType::class, 'subscription_type_id');
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'subscription_id');
    }

    public function deliveries()
    {
        return $this->hasMany(SubscriptionDelivery::class);
    }
}
