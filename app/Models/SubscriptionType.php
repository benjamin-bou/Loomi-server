<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SubscriptionType extends Model
{
    use HasFactory;

    protected $fillable = [
        'label',
        'description',
        'price',
        'recurrence',
        'delivery',
        'return_policy',
    ];

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    /**
     * Relation vers les avis du type d'abonnement (relation polymorphique)
     */
    public function reviews()
    {
        return $this->morphMany(Review::class, 'reviewable');
    }

    /**
     * Calculer la note moyenne du type d'abonnement
     */
    public function averageRating()
    {
        return $this->reviews()->avg('rating');
    }

    /**
     * Compter le nombre total d'avis
     */
    public function reviewsCount()
    {
        return $this->reviews()->count();
    }

    /**
     * Compter le nombre total d'avis (alias pour les tests)
     */
    public function totalReviews()
    {
        return $this->reviewsCount();
    }
}
