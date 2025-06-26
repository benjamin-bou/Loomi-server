<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'reviewable_id',
        'reviewable_type',
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
     * Relation polymorphique vers l'élément évalué (Box ou Subscription)
     */
    public function reviewable()
    {
        return $this->morphTo();
    }

    /**
     * Relation vers la boîte évaluée (pour la compatibilité)
     */
    public function box()
    {
        return $this->reviewable();
    }
}
