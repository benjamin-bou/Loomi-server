<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Box extends Model
{
    /** @use HasFactory<\Database\Factories\BoxFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'description',
        'base_price',
        'active',
        'box_category_id',
    ];

    public function items()
    {
        return $this->belongsToMany(Item::class)->withPivot('quantity');
    }

    public function category()
    {
        return $this->belongsTo(BoxCategory::class, 'box_category_id');
    }

    /**
     * Relation vers les avis de la boîte
     */
    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    /**
     * Calculer la note moyenne de la boîte
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
