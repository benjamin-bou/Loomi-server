<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Order;
use App\Models\Box;
use App\Models\BoxCategory;
use App\Models\PaymentMethodType;
use App\Models\GiftCard;
use App\Models\GiftCardType;
use App\Models\SubscriptionType;
use Illuminate\Foundation\Testing\RefreshDatabase;

class OrderTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Créer les données de test nécessaires
        $this->category = BoxCategory::factory()->create();
        $this->box = Box::factory()->create([
            'box_category_id' => $this->category->id,
            'base_price' => 19.99
        ]);

        $this->paymentMethodType = PaymentMethodType::factory()->create([
            'name' => 'Credit Card'
        ]);

        $this->subscriptionType = SubscriptionType::factory()->create([
            'name' => 'Mensuel',
            'duration_months' => 1,
            'price' => 24.99
        ]);
    }

    /** @test */
    public function authenticated_user_can_get_their_orders()
    {
        $user = $this->createUser();
        $token = $this->getJWTToken($user);

        // Créer quelques commandes pour l'utilisateur
        $orders = Order::factory()->count(3)->create(['user_id' => $user->id]);

        // Créer une commande pour un autre utilisateur
        $otherUser = $this->createUser();
        Order::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/orders');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'user',
                'orders' => [
                    '*' => [
                        'id',
                        'order_number',
                        'total_amount',
                        'status',
                        'created_at'
                    ]
                ]
            ])
            ->assertJsonCount(3, 'orders'); // Seulement les commandes de l'utilisateur
    }

    /** @test */
    public function unauthenticated_user_cannot_get_orders()
    {
        $response = $this->getJson('/api/orders');

        $response->assertStatus(401);
    }

    /** @test */
    public function user_can_create_order_with_boxes()
    {
        $user = $this->createUser();
        $token = $this->getJWTToken($user);

        $orderData = [
            'items' => [
                [
                    'type' => 'box',
                    'id' => $this->box->id,
                    'quantity' => 2
                ]
            ],
            'payment_method' => 'cb'
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/order', $orderData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'order' => [
                    'id',
                    'order_number',
                    'total_amount',
                    'status'
                ]
            ]);

        // Vérifier que la commande a été créée en base
        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
            'total_amount' => $this->box->base_price * 2,
            'status' => 'pending'
        ]);
    }

    /** @test */
    public function user_can_create_order_with_subscription()
    {
        $user = $this->createUser();
        $token = $this->getJWTToken($user);

        $orderData = [
            'items' => [
                [
                    'type' => 'subscription',
                    'id' => $this->subscriptionType->id,
                    'quantity' => 1
                ]
            ],
            'payment_method' => 'cb'
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/order', $orderData);

        $response->assertStatus(201);

        // Vérifier que la commande et l'abonnement ont été créés
        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
            'total_amount' => $this->subscriptionType->price,
            'status' => 'pending'
        ]);

        $this->assertDatabaseHas('subscriptions', [
            'subscription_type_id' => $this->subscriptionType->id,
            'status' => 'active'
        ]);
    }

    /** @test */
    public function user_can_create_order_with_gift_card()
    {
        $user = $this->createUser();
        $token = $this->getJWTToken($user);

        $giftCardType = GiftCardType::factory()->create([
            'name' => 'Carte Cadeau 50€',
            'base_price' => 50.00
        ]);

        $orderData = [
            'items' => [
                [
                    'type' => 'gift_card',
                    'id' => $giftCardType->id,
                    'quantity' => 1
                ]
            ],
            'payment_method' => 'cb'
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/order', $orderData);

        $response->assertStatus(201);

        // Vérifier que la commande et la carte cadeau ont été créées
        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
            'total_amount' => $giftCardType->base_price,
            'status' => 'pending'
        ]);

        $this->assertDatabaseHas('gift_cards', [
            'gift_card_type_id' => $giftCardType->id
        ]);
    }

    /** @test */
    public function user_cannot_create_order_with_empty_cart()
    {
        $user = $this->createUser();
        $token = $this->getJWTToken($user);

        $orderData = [
            'items' => [],
            'payment_method' => 'cb'
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/order', $orderData);

        $response->assertStatus(400)
            ->assertJson(['error' => 'Utilisateur non authentifié ou panier vide']);
    }

    /** @test */
    public function unauthenticated_user_cannot_create_order()
    {
        $orderData = [
            'items' => [
                [
                    'type' => 'box',
                    'id' => $this->box->id,
                    'quantity' => 1
                ]
            ],
            'payment_method' => 'cb'
        ];

        $response = $this->postJson('/api/order', $orderData);

        $response->assertStatus(401);
    }

    /** @test */
    public function user_can_get_payment_methods()
    {
        $response = $this->getJson('/api/payment-methods');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'payment_methods' => [
                    '*' => [
                        'key',
                        'label'
                    ]
                ]
            ]);
    }

    /** @test */
    public function user_can_create_mixed_order()
    {
        $user = $this->createUser();
        $token = $this->getJWTToken($user);

        $giftCardType = GiftCardType::factory()->create([
            'base_price' => 25.00
        ]);

        $orderData = [
            'items' => [
                [
                    'type' => 'box',
                    'id' => $this->box->id,
                    'quantity' => 1
                ],
                [
                    'type' => 'gift_card',
                    'id' => $giftCardType->id,
                    'quantity' => 1
                ]
            ],
            'payment_method' => 'cb'
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/order', $orderData);

        $response->assertStatus(201);

        $expectedTotal = $this->box->base_price + $giftCardType->base_price;

        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
            'total_amount' => $expectedTotal,
            'status' => 'pending'
        ]);
    }

    /** @test */
    public function order_number_is_unique_and_generated()
    {
        $user = $this->createUser();
        $token = $this->getJWTToken($user);

        $orderData = [
            'items' => [
                [
                    'type' => 'box',
                    'id' => $this->box->id,
                    'quantity' => 1
                ]
            ],
            'payment_method' => 'cb'
        ];

        // Créer deux commandes
        $response1 = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/order', $orderData);

        $response2 = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/order', $orderData);

        $response1->assertStatus(201);
        $response2->assertStatus(201);

        // Vérifier que les numéros de commande sont différents
        $order1 = json_decode($response1->getContent(), true);
        $order2 = json_decode($response2->getContent(), true);

        $this->assertNotEquals(
            $order1['order']['order_number'],
            $order2['order']['order_number']
        );
    }
}
