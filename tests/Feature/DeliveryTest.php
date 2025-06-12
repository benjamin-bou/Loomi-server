<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Order;
use App\Models\Box;
use App\Models\BoxCategory;
use App\Models\BoxOrder;
use App\Models\Subscription;
use App\Models\SubscriptionType;
use App\Models\SubscriptionDelivery;
use App\Models\Review;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;
use PHPUnit\Framework\Attributes\Test;

class DeliveryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->category = BoxCategory::factory()->create();
        $this->box = Box::factory()->create([
            'box_category_id' => $this->category->id
        ]);
        $this->subscriptionType = SubscriptionType::factory()->create([
            'label' => 'Abonnement Mensuel'
        ]);
    }

    #[Test]
    public function authenticated_user_can_get_their_deliveries()
    {
        $user = $this->createUser();
        $token = $this->getJWTToken($user);

        // Créer une commande avec boîte pour l'utilisateur
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'status' => 'delivered',
            'delivery_date' => now()
        ]);

        $boxOrder = BoxOrder::factory()->create([
            'order_id' => $order->id,
            'box_id' => $this->box->id,
            'quantity' => 1
        ]);

        // Créer un abonnement avec livraison
        $subscription = Subscription::factory()->create([
            'subscription_type_id' => $this->subscriptionType->id
        ]);

        $subscriptionOrder = Order::factory()->create([
            'user_id' => $user->id,
            'subscription_id' => $subscription->id
        ]);

        $subscriptionDelivery = SubscriptionDelivery::factory()->create([
            'subscription_id' => $subscription->id,
            'box_id' => $this->box->id,
            'delivered_at' => now()
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/profile/deliveries');

        $response->assertStatus(200)
            ->assertJsonStructure([
                '*' => [
                    'id',
                    'delivery_type',
                    'box_id',
                    'box_name',
                    'delivery_date',
                    'status',
                    'can_review'
                ]
            ]);

        // Vérifier qu'on a au moins les deux livraisons
        $deliveries = $response->json();
        $this->assertGreaterThanOrEqual(2, count($deliveries));
    }

    #[Test]
    public function user_can_only_see_their_own_deliveries()
    {
        $user1 = $this->createUser();
        $user2 = $this->createUser();
        $token1 = $this->getJWTToken($user1);

        // Créer une commande pour user1
        $order1 = Order::factory()->create([
            'user_id' => $user1->id,
            'status' => 'delivered'
        ]);

        BoxOrder::factory()->create([
            'order_id' => $order1->id,
            'box_id' => $this->box->id
        ]);

        // Créer une commande pour user2
        $order2 = Order::factory()->create([
            'user_id' => $user2->id,
            'status' => 'delivered'
        ]);

        BoxOrder::factory()->create([
            'order_id' => $order2->id,
            'box_id' => $this->box->id
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token1,
        ])->getJson('/api/profile/deliveries');

        $response->assertStatus(200);

        $deliveries = $response->json();

        // Vérifier que user1 ne voit que ses propres livraisons
        foreach ($deliveries as $delivery) {
            // Si c'est une livraison de commande directe, vérifier via order_id
            if ($delivery['delivery_type'] === 'order') {
                $orderId = explode('_', $delivery['id'])[1];
                $this->assertEquals($order1->id, $orderId);
            }
        }
    }

    #[Test]
    public function unauthenticated_user_cannot_get_deliveries()
    {
        $response = $this->getJson('/api/profile/deliveries');

        $response->assertStatus(401);
    }

    #[Test]
    public function delivery_shows_correct_review_status()
    {
        $user = $this->createUser();
        $token = $this->getJWTToken($user);

        // Créer une commande livrée
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'status' => 'delivered'
        ]);

        BoxOrder::factory()->create([
            'order_id' => $order->id,
            'box_id' => $this->box->id
        ]);

        // Première requête : peut laisser un avis
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/profile/deliveries');

        $deliveries = $response->json();
        $delivery = collect($deliveries)->firstWhere('delivery_type', 'order');
        $this->assertTrue($delivery['can_review']);

        // Créer un avis
        Review::factory()->create([
            'user_id' => $user->id,
            'box_id' => $this->box->id
        ]);

        // Deuxième requête : ne peut plus laisser d'avis
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/profile/deliveries');

        $deliveries = $response->json();
        $delivery = collect($deliveries)->firstWhere('delivery_type', 'order');
        $this->assertFalse($delivery['can_review']);
    }

    #[Test]
    public function delivery_includes_subscription_information()
    {
        $user = $this->createUser();
        $token = $this->getJWTToken($user);

        // Créer un abonnement avec livraison
        $subscription = Subscription::factory()->create([
            'subscription_type_id' => $this->subscriptionType->id
        ]);

        $order = Order::factory()->create([
            'user_id' => $user->id,
            'subscription_id' => $subscription->id
        ]);

        $subscriptionDelivery = SubscriptionDelivery::factory()->create([
            'subscription_id' => $subscription->id,
            'box_id' => $this->box->id,
            'delivered_at' => now()
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/profile/deliveries');

        $response->assertStatus(200);

        $deliveries = $response->json();
        $subscriptionDelivery = collect($deliveries)->firstWhere('delivery_type', 'subscription');

        $this->assertNotNull($subscriptionDelivery);
        $this->assertEquals('subscription', $subscriptionDelivery['delivery_type']);
        $this->assertEquals($this->box->id, $subscriptionDelivery['box_id']);
        $this->assertEquals($this->box->name, $subscriptionDelivery['box_name']);
        $this->assertEquals('delivered', $subscriptionDelivery['status']);
    }

    #[Test]
    public function delivery_includes_order_information()
    {
        $user = $this->createUser();
        $token = $this->getJWTToken($user);

        $order = Order::factory()->create([
            'user_id' => $user->id,
            'status' => 'delivered',
            'order_number' => 'ORD2024001'
        ]);

        BoxOrder::factory()->create([
            'order_id' => $order->id,
            'box_id' => $this->box->id,
            'quantity' => 2
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/profile/deliveries');

        $response->assertStatus(200);

        $deliveries = $response->json();
        $orderDelivery = collect($deliveries)->firstWhere('delivery_type', 'order');

        $this->assertNotNull($orderDelivery);
        $this->assertEquals('order', $orderDelivery['delivery_type']);
        $this->assertEquals($this->box->id, $orderDelivery['box_id']);
        $this->assertEquals($this->box->name, $orderDelivery['box_name']);
        $this->assertEquals('ORD2024001', $orderDelivery['order_number']);
        $this->assertEquals(2, $orderDelivery['quantity']);
    }

    #[Test]
    public function only_delivered_orders_appear_in_deliveries()
    {
        $user = $this->createUser();
        $token = $this->getJWTToken($user);

        // Créer différentes commandes avec différents statuts
        $pendingOrder = Order::factory()->create([
            'user_id' => $user->id,
            'status' => 'pending'
        ]);

        $shippedOrder = Order::factory()->create([
            'user_id' => $user->id,
            'status' => 'shipped'
        ]);

        $deliveredOrder = Order::factory()->create([
            'user_id' => $user->id,
            'status' => 'delivered'
        ]);

        // Créer des BoxOrders pour chacune
        BoxOrder::factory()->create([
            'order_id' => $pendingOrder->id,
            'box_id' => $this->box->id
        ]);

        BoxOrder::factory()->create([
            'order_id' => $shippedOrder->id,
            'box_id' => $this->box->id
        ]);

        BoxOrder::factory()->create([
            'order_id' => $deliveredOrder->id,
            'box_id' => $this->box->id
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/profile/deliveries');

        $response->assertStatus(200);

        $deliveries = $response->json();
        $orderDeliveries = collect($deliveries)->where('delivery_type', 'order');

        // Seulement la commande livrée devrait apparaître
        $this->assertEquals(1, $orderDeliveries->count());

        $delivery = $orderDeliveries->first();
        $this->assertEquals('delivered', $delivery['status']);
    }

    #[Test]
    public function empty_deliveries_list_when_user_has_no_orders()
    {
        $user = $this->createUser();
        $token = $this->getJWTToken($user);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/profile/deliveries');

        $response->assertStatus(200)
            ->assertJson([]);
    }

    #[Test]
    public function delivery_dates_are_properly_formatted()
    {
        $user = $this->createUser();
        $token = $this->getJWTToken($user);

        $deliveryDate = Carbon::create(2024, 6, 15, 10, 30, 0);

        $order = Order::factory()->create([
            'user_id' => $user->id,
            'status' => 'delivered',
            'delivery_date' => $deliveryDate
        ]);

        BoxOrder::factory()->create([
            'order_id' => $order->id,
            'box_id' => $this->box->id
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/profile/deliveries');

        $response->assertStatus(200);

        $deliveries = $response->json();
        $delivery = collect($deliveries)->firstWhere('delivery_type', 'order');

        $this->assertNotNull($delivery['delivery_date']);
        // Vérifier que la date est correctement formatée
        $this->assertStringContainsString('2024-06-15', $delivery['delivery_date']);
    }
}
