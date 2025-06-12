<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Subscription;
use App\Models\SubscriptionType;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;
use PHPUnit\Framework\Attributes\Test;

class SubscriptionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->subscriptionType = SubscriptionType::factory()->create([
            'label' => 'Abonnement Mensuel',
            'price' => 24.99,
        ]);
    }

    #[Test]
    public function user_can_get_list_of_subscription_types()
    {        // Créer un autre type d'abonnement
        $anotherType = SubscriptionType::factory()->create();

        $response = $this->getJson('/api/subscriptions');

        $response->assertStatus(200)->assertJsonStructure([
            '*' => [
                'id',
                'label',
                'price',
                'recurrence'
            ]
        ]);        // Le endpoint retourne tous les types
        $response->assertJsonFragment(['id' => $this->subscriptionType->id])
            ->assertJsonFragment(['id' => $anotherType->id]);
    }

    #[Test]
    public function user_can_get_subscription_type_details()
    {
        $response = $this->getJson("/api/subscriptions/{$this->subscriptionType->id}");
        $response->assertStatus(200)
            ->assertJson([
                'id' => $this->subscriptionType->id,
                'label' => $this->subscriptionType->label,
                'price' => $this->subscriptionType->price,
            ]);
    }

    #[Test]
    public function user_gets_404_for_nonexistent_subscription_type()
    {
        $response = $this->getJson('/api/subscriptions/999');

        $response->assertStatus(404);
    }

    #[Test]
    public function authenticated_user_can_get_current_subscription()
    {
        $user = $this->createUser();
        $token = $this->getJWTToken($user);

        // Créer un abonnement actif
        $subscription = Subscription::factory()->create([
            'subscription_type_id' => $this->subscriptionType->id,
            'start_date' => now()->toDateString(),
            'end_date' => now()->addMonth()->toDateString(),
            'status' => 'active'
        ]);

        $order = Order::factory()->create([
            'user_id' => $user->id,
            'subscription_id' => $subscription->id,
            'active' => true
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/my-subscription');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'subscription' => [
                    'id',
                    'subscription_type_id',
                    'start_date',
                    'end_date',
                    'status',
                    'type' => [
                        'label',
                        'price'
                    ]
                ],
                'user'
            ]);
    }

    #[Test]
    public function user_with_no_subscription_gets_null()
    {
        $user = $this->createUser();
        $token = $this->getJWTToken($user);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/my-subscription');

        $response->assertStatus(200)
            ->assertJson([
                'subscription' => null,
                'user' => [
                    'id' => $user->id
                ]
            ]);
    }

    #[Test]
    public function unauthenticated_user_cannot_get_subscription()
    {
        $response = $this->getJson('/api/my-subscription');

        $response->assertStatus(401);
    }

    #[Test]
    public function user_can_cancel_active_subscription()
    {
        $user = $this->createUser();
        $token = $this->getJWTToken($user);

        // Créer un abonnement actif
        $subscription = Subscription::factory()->create([
            'subscription_type_id' => $this->subscriptionType->id,
            'start_date' => now()->toDateString(),
            'end_date' => now()->addMonth()->toDateString(),
            'status' => 'active',
            'auto_renew' => true
        ]);

        $order = Order::factory()->create([
            'user_id' => $user->id,
            'subscription_id' => $subscription->id,
            'active' => true
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/cancel-subscription');

        $response->assertStatus(200)
            ->assertJson(['message' => 'Abonnement annulé avec succès']);

        // Vérifier que l'abonnement est marqué comme annulé
        $this->assertDatabaseHas('subscriptions', [
            'id' => $subscription->id,
            'status' => 'cancelled',
            'auto_renew' => false
        ]);

        // Vérifier que la commande est désactivée
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'active' => false
        ]);
    }

    #[Test]
    public function user_cannot_cancel_nonexistent_subscription()
    {
        $user = $this->createUser();
        $token = $this->getJWTToken($user);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/cancel-subscription');

        $response->assertStatus(404)
            ->assertJson(['error' => 'Aucun abonnement actif trouvé']);
    }

    #[Test]
    public function user_cannot_cancel_already_cancelled_subscription()
    {
        $user = $this->createUser();
        $token = $this->getJWTToken($user);

        // Créer un abonnement déjà annulé
        $subscription = Subscription::factory()->create([
            'subscription_type_id' => $this->subscriptionType->id,
            'status' => 'cancelled'
        ]);

        $order = Order::factory()->create([
            'user_id' => $user->id,
            'subscription_id' => $subscription->id,
            'active' => false
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/cancel-subscription');

        $response->assertStatus(404)
            ->assertJson(['error' => 'Aucun abonnement actif trouvé']);
    }

    #[Test]
    public function unauthenticated_user_cannot_cancel_subscription()
    {
        $response = $this->postJson('/api/cancel-subscription');

        $response->assertStatus(401);
    }

    #[Test]
    public function subscription_type_has_correct_structure()
    {
        $subscriptionType = SubscriptionType::factory()->create([
            'label' => 'Abonnement Trimestriel',
            'description' => 'Abonnement de 3 mois',
            'price' => 69.99,
        ]);

        $response = $this->getJson("/api/subscriptions/{$subscriptionType->id}");

        $response->assertStatus(200)->assertJsonStructure([
            'id',
            'label',
            'description',
            'price',
            'recurrence',
            'created_at',
            'updated_at'
        ]);
    }

    #[Test]
    public function user_can_only_access_their_own_subscription()
    {
        $user1 = $this->createUser();
        $user2 = $this->createUser();
        $token1 = $this->getJWTToken($user1);

        // Créer un abonnement pour user2
        $subscription = Subscription::factory()->create([
            'subscription_type_id' => $this->subscriptionType->id,
            'status' => 'active'
        ]);

        $order = Order::factory()->create([
            'user_id' => $user2->id,
            'subscription_id' => $subscription->id,
            'active' => true
        ]);

        // user1 ne devrait pas voir l'abonnement de user2
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token1,
        ])->getJson('/api/my-subscription');

        $response->assertStatus(200)
            ->assertJson(['subscription' => null]);
    }

    #[Test]
    public function subscription_with_expired_end_date_is_handled_correctly()
    {
        $user = $this->createUser();
        $token = $this->getJWTToken($user);

        // Créer un abonnement expiré
        $subscription = Subscription::factory()->create([
            'subscription_type_id' => $this->subscriptionType->id,
            'start_date' => now()->subMonths(2)->toDateString(),
            'end_date' => now()->subMonth()->toDateString(), // Expiré le mois dernier
            'status' => 'active' // Toujours marqué comme actif en DB
        ]);

        $order = Order::factory()->create([
            'user_id' => $user->id,
            'subscription_id' => $subscription->id,
            'active' => true
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/my-subscription');

        $response->assertStatus(200);

        // Vérifier que l'abonnement est retourné même s'il est expiré
        // (la logique métier peut gérer l'expiration côté client)
        $responseData = $response->json();
        $this->assertNotNull($responseData['subscription']);
        $this->assertEquals($subscription->id, $responseData['subscription']['id']);
    }
}
