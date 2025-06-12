<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use App\Models\Order;
use App\Models\Review;
use App\Models\Subscription;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;

class UserModelTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function user_has_many_orders()
    {
        $user = User::factory()->create();
        $orders = Order::factory()->count(3)->create(['user_id' => $user->id]);

        $this->assertInstanceOf('Illuminate\Database\Eloquent\Collection', $user->orders);
        $this->assertCount(3, $user->orders);
        $this->assertInstanceOf(Order::class, $user->orders->first());
    }

    #[Test]
    public function user_has_many_reviews()
    {
        $user = User::factory()->create();
        $reviews = Review::factory()->count(2)->create(['user_id' => $user->id]);

        $this->assertInstanceOf('Illuminate\Database\Eloquent\Collection', $user->reviews);
        $this->assertCount(2, $user->reviews);
        $this->assertInstanceOf(Review::class, $user->reviews->first());
    }

    #[Test]
    public function user_has_subscriptions_through_orders()
    {
        $user = User::factory()->create();

        $subscription = Subscription::factory()->create();
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'subscription_id' => $subscription->id
        ]);

        $userSubscriptions = $user->subscriptions;

        $this->assertInstanceOf('Illuminate\Database\Eloquent\Collection', $userSubscriptions);
        $this->assertCount(1, $userSubscriptions);
        $this->assertEquals($subscription->id, $userSubscriptions->first()->id);
    }

    #[Test]
    public function user_password_is_hashed()
    {
        $user = User::factory()->create([
            'password' => 'plaintext-password'
        ]);

        $this->assertNotEquals('plaintext-password', $user->password);
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('plaintext-password', $user->password));
    }

    #[Test]
    public function user_has_jwt_custom_claims()
    {
        $user = User::factory()->create([
            'role' => 'admin',
            'first_name' => 'Jean',
            'last_name' => 'Dupont'
        ]);

        $claims = $user->getJWTCustomClaims();

        $this->assertArrayHasKey('role', $claims);
        $this->assertArrayHasKey('firstName', $claims);
        $this->assertArrayHasKey('lastName', $claims);
        $this->assertEquals('admin', $claims['role']);
        $this->assertEquals('Jean', $claims['firstName']);
        $this->assertEquals('Dupont', $claims['lastName']);
    }

    #[Test]
    public function user_can_get_jwt_identifier()
    {
        $user = User::factory()->create();

        $this->assertEquals($user->id, $user->getJWTIdentifier());
    }

    #[Test]
    public function user_fillable_attributes_are_correct()
    {
        $fillable = [
            'last_name',
            'first_name',
            'email',
            'password',
            'role',
            'address',
        ];

        $user = new User();

        $this->assertEquals($fillable, $user->getFillable());
    }

    #[Test]
    public function user_hidden_attributes_include_password()
    {
        $user = new User();
        $hidden = $user->getHidden();

        $this->assertContains('password', $hidden);
        $this->assertContains('remember_token', $hidden);
    }

    #[Test]
    public function user_email_verified_at_is_cast_to_datetime()
    {
        $user = User::factory()->create([
            'email_verified_at' => '2024-01-01 10:00:00'
        ]);

        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $user->email_verified_at);
    }
}
