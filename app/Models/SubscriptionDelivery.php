<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SubscriptionDelivery extends Model
{
    use HasFactory;

    protected $fillable = [
        'box_id',
        'subscription_id',
        'delivered_at',
    ];

    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }

    public function box()
    {
        return $this->belongsTo(Box::class);
    }
}
