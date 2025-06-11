<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Box;
use App\Models\BoxCategory;
use App\Models\Order;
use App\Models\BoxOrder;
use App\Models\Review;
use App\Models\GiftCard;
use App\Models\GiftCardType;
use App\Models\Subscription;
use App\Models\SubscriptionType;
use App\Models\PaymentMethodType;
use Illuminate\Foundation\Testing\RefreshDatabase;

class IntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Créer les données de base nécessaires
        $this->category = BoxCategory::factory()->create([
            'short_name' => 'test-category'
        ]);

        $this->box = Box::factory()->create([
            'box_category_id' => $this->category->id,
            'name' => 'Boîte Test',
            'base_price' => 25.99
        ]);

        $this->subscriptionType = SubscriptionType::factory()->create([
            'name' => 'Abonnement Test',
            'price' => 29.99
        ]);

        $this->giftCardType = GiftCardType::factory()->create([
            'name' => 'Carte Cadeau Test',
            'base_price' => 50.00
        ]);

        $this->paymentMethodType = PaymentMethodType::factory()->create([
            'name' => 'Credit Card'
        ]);
    }

    /** @test */
    public function complete_user_journey_box_purchase_and_review()
    {
        // 1. Créer un utilisateur
        $user = $this->createUser([
            'email' => 'test@example.com',
            'first_name' => 'Jean',
            'last_name' => 'Dupont'
        ]);
        $token = $this->getJWTToken($user);

        // 2. L'utilisateur consulte les boîtes disponibles
        $response = $this->getJson('/api/boxes');
        $response->assertStatus(200);

        $boxes = $response->json();
        $this->assertNotEmpty($boxes);

        // 3. L'utilisateur passe une commande
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

        $response->assertStatus(201);
        $orderResponse = $response->json();

        // 4. Vérifier que la commande a été créée correctement
        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
            'total_amount' => $this->box->base_price * 2,
            'status' => 'pending'
        ]);

        $order = Order::where('user_id', $user->id)->first();

        // 5. Simuler la livraison
        $order->update(['status' => 'delivered']);

        // 6. L'utilisateur consulte ses livraisons
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/profile/deliveries');

        $response->assertStatus(200);
        $deliveries = $response->json();
        $this->assertNotEmpty($deliveries);

        // 7. L'utilisateur laisse un avis
        $reviewData = [
            'box_id' => $this->box->id,
            'rating' => 4.5,
            'comment' => 'Excellente boîte, je recommande !'
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/reviews', $reviewData);

        $response->assertStatus(201);

        // 8. Vérifier que l'avis a été créé
        $this->assertDatabaseHas('reviews', [
            'user_id' => $user->id,
            'box_id' => $this->box->id,
            'rating' => 4.5,
            'comment' => 'Excellente boîte, je recommande !'
        ]);

        // 9. Consulter les avis de la boîte
        $response = $this->getJson("/api/boxes/{$this->box->id}/reviews");
        $response->assertStatus(200);

        $reviewsData = $response->json();
        $this->assertEquals(1, $reviewsData['total_reviews']);
        $this->assertEquals(4.5, $reviewsData['average_rating']);
    }

    /** @test */
    public function complete_subscription_journey()
    {
        $user = $this->createUser();
        $token = $this->getJWTToken($user);

        // 1. Consulter les types d'abonnement
        $response = $this->getJson('/api/subscriptions');
        $response->assertStatus(200);

        // 2. Souscrire à un abonnement
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

        // 3. Vérifier l'abonnement créé
        $this->assertDatabaseHas('subscriptions', [
            'subscription_type_id' => $this->subscriptionType->id,
            'status' => 'active'
        ]);

        // 4. Consulter l'abonnement actuel
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/my-subscription');

        $response->assertStatus(200);
        $subscriptionData = $response->json();
        $this->assertNotNull($subscriptionData['subscription']);

        // 5. Annuler l'abonnement
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/cancel-subscription');

        $response->assertStatus(200);

        // 6. Vérifier l'annulation
        $subscription = Subscription::where('subscription_type_id', $this->subscriptionType->id)->first();
        $this->assertEquals('cancelled', $subscription->status);
    }

    /** @test */
    public function complete_gift_card_journey()
    {
        $user = $this->createUser();
        $token = $this->getJWTToken($user);

        // 1. Acheter une carte cadeau
        $orderData = [
            'items' => [
                [
                    'type' => 'gift_card',
                    'id' => $this->giftCardType->id,
                    'quantity' => 1
                ]
            ],
            'payment_method' => 'cb'
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/order', $orderData);

        $response->assertStatus(201);

        // 2. Vérifier que la carte a été créée
        $giftCard = GiftCard::where('gift_card_type_id', $this->giftCardType->id)->first();
        $this->assertNotNull($giftCard);
        $this->assertNotNull($giftCard->code);

        // 3. Activer la carte cadeau avec un autre utilisateur
        $otherUser = $this->createUser(['email' => 'other@example.com']);
        $otherToken = $this->getJWTToken($otherUser);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $otherToken,
        ])->postJson('/api/gift-cards/activate', [
            'code' => $giftCard->code
        ]);

        $response->assertStatus(200);

        // 4. Vérifier l'activation
        $giftCard->refresh();
        $this->assertEquals($otherUser->id, $giftCard->activated_by);
        $this->assertNotNull($giftCard->used_at);

        // 5. Consulter les cartes cadeaux de l'utilisateur
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $otherToken,
        ])->getJson('/api/my-gift-cards');

        $response->assertStatus(200);
        $giftCards = $response->json();
        $this->assertCount(1, $giftCards);
    }

    /** @test */
    public function admin_can_manage_boxes()
    {
        $admin = $this->createAdmin();
        $token = $this->getJWTToken($admin);

        // 1. Consulter toutes les boîtes (y compris inactives)
        $inactiveBox = Box::factory()->create([
            'box_category_id' => $this->category->id,
            'active' => false
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/admin/boxes');

        $response->assertStatus(200);
        $boxes = $response->json();
        $this->assertGreaterThanOrEqual(2, count($boxes)); // Au moins la boîte active et inactive

        // 2. Modifier une boîte
        $updateData = [
            'name' => 'Boîte Modifiée',
            'description' => 'Description mise à jour',
            'base_price' => 35.99,
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->putJson("/api/admin/boxes/{$this->box->id}", $updateData);

        $response->assertStatus(200);

        // 3. Vérifier la modification
        $this->assertDatabaseHas('boxes', [
            'id' => $this->box->id,
            'name' => 'Boîte Modifiée',
            'description' => 'Description mise à jour',
            'base_price' => 35.99,
        ]);
    }

    /** @test */
    public function payment_methods_are_properly_handled()
    {
        // 1. Consulter les méthodes de paiement disponibles
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

        $paymentMethods = $response->json()['payment_methods'];
        $this->assertNotEmpty($paymentMethods);

        // 2. Vérifier qu'on a au moins les méthodes de base
        $methodKeys = collect($paymentMethods)->pluck('key')->toArray();
        $this->assertContains('cb', $methodKeys);
    }

    /** @test */
    public function mixed_order_calculation_is_correct()
    {
        $user = $this->createUser();
        $token = $this->getJWTToken($user);

        // Commande mixte : boîte + carte cadeau
        $orderData = [
            'items' => [
                [
                    'type' => 'box',
                    'id' => $this->box->id,
                    'quantity' => 2
                ],
                [
                    'type' => 'gift_card',
                    'id' => $this->giftCardType->id,
                    'quantity' => 1
                ]
            ],
            'payment_method' => 'cb'
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/order', $orderData);

        $response->assertStatus(201);

        $expectedTotal = ($this->box->base_price * 2) + $this->giftCardType->base_price;

        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
            'total_amount' => $expectedTotal
        ]);
    }
}
