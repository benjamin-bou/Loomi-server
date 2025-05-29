<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'box_id',
        'rating',
        'comment',
    ];

    protected $casts = [
        'rating' => 'decimal:1',
    ];

    /**
     * Relation vers l'utilisateur qui a laissé l'avis
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relation vers la boîte évaluée
     */
    public function box()
    {
        return $this->belongsTo(Box::class);
    }
}
