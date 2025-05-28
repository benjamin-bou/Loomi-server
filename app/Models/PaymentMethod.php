<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    /** @use HasFactory<\Database\Factories\PaymentMethodFactory> */
    use HasFactory;

    protected $fillable = [
        'payment_method_type_id',
        'gift_card_id',
        'amount',
        'order_id',
    ];

    public function paymentMethodType()
    {
        return $this->belongsTo(PaymentMethodType::class);
    }

    public function giftCard()
    {
        return $this->belongsTo(GiftCard::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
