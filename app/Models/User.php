<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'last_name',
        'first_name',
        'email',
        'password',
        'role',
        'address',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [
            'role' => $this->role,
            'firstName' => $this->first_name,
            'lastName' => $this->last_name,
        ];
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function subscriptions()
    {
        // Retourne les subscriptions via les orders de l'utilisateur
        $subscriptionIds = $this->orders()->whereNotNull('subscription_id')->pluck('subscription_id');
        return Subscription::whereIn('id', $subscriptionIds);
    }

    /**
     * Attribut pour récupérer les subscriptions comme collection
     */
    public function getSubscriptionsAttribute()
    {
        return $this->subscriptions()->get();
    }

    /**
     * Relation vers les avis laissés par l'utilisateur
     */
    public function reviews()
    {
        return $this->hasMany(Review::class);
    }
}
