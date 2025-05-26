<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BoxOrder extends Model
{
    use HasFactory;
    protected $fillable = [
        'order_id',
        'box_id',
        'quantity',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function box()
    {
        return $this->belongsTo(Box::class, 'box_id');
    }
}
