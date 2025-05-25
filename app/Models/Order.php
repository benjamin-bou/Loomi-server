<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    /** @use HasFactory<\Database\Factories\OrderFactory> */
    use HasFactory;

    public function paymentMethods()
    {
        return $this->hasMany(PaymentMethod::class);
    }
    public function subscription()
    {
        return $this->belongsTo(Subscription::class, 'subscription_id');
    }
    // Ajout de la relation boxes() pour la table pivot box_orders
    public function boxes()
    {
        return $this->belongsToMany(\App\Models\Box::class, 'box_orders', 'order_id', 'box_id')->withPivot('quantity');
    }
}
