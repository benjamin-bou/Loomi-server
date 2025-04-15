<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Box extends Model
{
    /** @use HasFactory<\Database\Factories\BoxFactory> */
    use HasFactory;

    public function items()
    {
        return $this->belongsToMany(Item::class)->withPivot('quantity');
    }
}
