<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GiftCard extends Model
{
    /** @use HasFactory<\Database\Factories\GiftCardFactory> */
    use HasFactory;

    protected $fillable = [
        'code',
        'expiration_date',
        'used_at',
        'order_id',
        'gift_card_type_id',
    ];

    /**
     * Relation vers GiftCardType
     */
    public function giftCardType()
    {
        return $this->belongsTo(GiftCardType::class);
    }

    /**
     * Relation vers Order
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
