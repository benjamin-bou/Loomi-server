<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    /** @use HasFactory<\Database\Factories\OrderFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'order_number',
        'total_amount',
        'status',
        'gift_card_id',
        'subscription_id',
        'active',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

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
    public function boxOrders()
    {
        return $this->hasMany(\App\Models\BoxOrder::class, 'order_id');
    }

    public function giftCard()
    {
        return $this->belongsTo(GiftCard::class, 'gift_card_id');
    }

    /**
     * Relation vers les gift cards créées par cette commande
     */
    public function createdGiftCards()
    {
        return $this->hasMany(GiftCard::class, 'order_id');
    }
}
