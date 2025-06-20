<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Box;
use App\Models\BoxCategory;
use App\Models\Review;
use App\Models\Order;
use App\Models\BoxOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;

class ReviewTest extends TestCase
{
    use RefreshDatabase;

    protected $category;
    protected $box;

    protected function setUp(): void
    {
        parent::setUp();

        $this->category = BoxCategory::factory()->create();
        $this->box = Box::create([
            'name' => 'Test Review Box',
            'description' => 'Box for review testing',
            'base_price' => 29.90,
            'active' => true,
            'quantity' => 20,
            'available_from' => now(),
            'box_category_id' => $this->category->id
        ]);
    }

    #[Test]
    public function user_can_create_review_for_received_box()
    {
        $user = $this->createUser();
        $token = $this->getJWTToken($user);

        // Créer une commande avec cette boîte pour l'utilisateur
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'status' => 'delivered'
        ]);

        BoxOrder::factory()->create([
            'order_id' => $order->id,
            'box_id' => $this->box->id,
            'quantity' => 1
        ]);

        $reviewData = [
            'box_id' => $this->box->id,
            'rating' => 4.5,
            'comment' => 'Excellente boîte, très satisfait !'
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/reviews', $reviewData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'review' => [
                    'id',
                    'user_id',
                    'box_id',
                    'rating',
                    'comment',
                    'user',
                    'box'
                ]
            ]);

        $this->assertDatabaseHas('reviews', [
            'user_id' => $user->id,
            'box_id' => $this->box->id,
            'rating' => 4.5,
            'comment' => $reviewData['comment']
        ]);
    }

    #[Test]
    public function user_cannot_create_review_for_box_not_received()
    {
        $user = $this->createUser();
        $token = $this->getJWTToken($user);

        $reviewData = [
            'box_id' => $this->box->id,
            'rating' => 4.5,
            'comment' => 'Tentative d\'avis sans avoir reçu la boîte'
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/reviews', $reviewData);

        $response->assertStatus(403)
            ->assertJson(['error' => 'Vous ne pouvez laisser un avis que pour des boîtes que vous avez reçues']);
    }

    #[Test]
    public function user_cannot_create_duplicate_review()
    {
        $user = $this->createUser();
        $token = $this->getJWTToken($user);

        // Créer une commande avec cette boîte
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'status' => 'delivered'
        ]);

        BoxOrder::factory()->create([
            'order_id' => $order->id,
            'box_id' => $this->box->id,
            'quantity' => 1
        ]);

        // Créer un premier avis
        Review::factory()->create([
            'user_id' => $user->id,
            'box_id' => $this->box->id,
            'rating' => 3.0
        ]);

        $reviewData = [
            'box_id' => $this->box->id,
            'rating' => 4.5,
            'comment' => 'Tentative de deuxième avis'
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/reviews', $reviewData);
        $response->assertStatus(409)
            ->assertJson(['error' => 'Vous avez déjà laissé un avis pour cette boîte']);
    }

    #[Test]
    public function unauthenticated_user_cannot_create_review()
    {
        $reviewData = [
            'box_id' => $this->box->id,
            'rating' => 4.5,
            'comment' => 'Tentative d\'avis sans authentification'
        ];

        $response = $this->postJson('/api/reviews', $reviewData);

        $response->assertStatus(401);
    }

    #[Test]
    public function user_cannot_create_review_with_invalid_rating()
    {
        $user = $this->createUser();
        $token = $this->getJWTToken($user);

        // Créer une commande avec cette boîte
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'status' => 'delivered'
        ]);

        BoxOrder::factory()->create([
            'order_id' => $order->id,
            'box_id' => $this->box->id,
            'quantity' => 1
        ]);

        $reviewData = [
            'box_id' => $this->box->id,
            'rating' => 6.0, // Rating trop élevé
            'comment' => 'Avis avec rating invalide'
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/reviews', $reviewData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['rating']);
    }

    #[Test]
    public function user_can_get_their_review_for_box()
    {
        $user = $this->createUser();
        $token = $this->getJWTToken($user);

        $review = Review::factory()->create([
            'user_id' => $user->id,
            'box_id' => $this->box->id,
            'rating' => 4.0,
            'comment' => 'Mon avis existant'
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson("/api/reviews/user/{$this->box->id}");

        $response->assertStatus(200)
            ->assertJson([
                'review' => [
                    'id' => $review->id,
                    'rating' => 4.0,
                    'comment' => 'Mon avis existant'
                ]
            ]);
    }

    #[Test]
    public function user_gets_null_when_no_review_exists()
    {
        $user = $this->createUser();
        $token = $this->getJWTToken($user);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson("/api/reviews/user/{$this->box->id}");

        $response->assertStatus(200)
            ->assertJson(['review' => null]);
    }

    #[Test]
    public function anyone_can_get_all_reviews_for_box()
    {
        // Créer plusieurs avis pour la boîte
        $users = User::factory()->count(3)->create();

        foreach ($users as $user) {
            Review::factory()->create([
                'user_id' => $user->id,
                'box_id' => $this->box->id
            ]);
        }

        $response = $this->getJson("/api/boxes/{$this->box->id}/reviews");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'reviews' => [
                    '*' => [
                        'id',
                        'rating',
                        'comment',
                        'created_at',
                        'user' => [
                            'id',
                            'first_name',
                            'last_name'
                        ]
                    ]
                ],
                'average_rating',
                'total_reviews',
                'rating_distribution'
            ])
            ->assertJsonCount(3, 'reviews');
    }

    #[Test]
    public function user_can_update_their_review()
    {
        $user = $this->createUser();
        $token = $this->getJWTToken($user);

        $review = Review::factory()->create([
            'user_id' => $user->id,
            'box_id' => $this->box->id,
            'rating' => 3.0,
            'comment' => 'Avis initial'
        ]);

        $updateData = [
            'rating' => 4.5,
            'comment' => 'Avis mis à jour'
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->putJson("/api/reviews/{$review->id}", $updateData);

        $response->assertStatus(200)
            ->assertJson(['message' => 'Avis mis à jour avec succès']);

        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
            'rating' => 4.5,
            'comment' => 'Avis mis à jour'
        ]);
    }

    #[Test]
    public function user_cannot_update_other_users_review()
    {
        $user = $this->createUser();
        $otherUser = $this->createUser();
        $token = $this->getJWTToken($user);

        $review = Review::factory()->create([
            'user_id' => $otherUser->id,
            'box_id' => $this->box->id
        ]);

        $updateData = [
            'rating' => 4.5,
            'comment' => 'Tentative de modification'
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->putJson("/api/reviews/{$review->id}", $updateData);

        $response->assertStatus(403)
            ->assertJson(['error' => 'Non autorisé à modifier cet avis']);
    }

    #[Test]
    public function user_can_delete_their_review()
    {
        $user = $this->createUser();
        $token = $this->getJWTToken($user);

        $review = Review::factory()->create([
            'user_id' => $user->id,
            'box_id' => $this->box->id
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->deleteJson("/api/reviews/{$review->id}");

        $response->assertStatus(200)
            ->assertJson(['message' => 'Avis supprimé avec succès']);

        $this->assertDatabaseMissing('reviews', ['id' => $review->id]);
    }

    #[Test]
    public function user_cannot_delete_other_users_review()
    {
        $user = $this->createUser();
        $otherUser = $this->createUser();
        $token = $this->getJWTToken($user);

        $review = Review::factory()->create([
            'user_id' => $otherUser->id,
            'box_id' => $this->box->id
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->deleteJson("/api/reviews/{$review->id}");

        $response->assertStatus(403);

        $this->assertDatabaseHas('reviews', ['id' => $review->id]);
    }
}
