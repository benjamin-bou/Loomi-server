<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Box;
use App\Models\BoxCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BoxTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Créer une catégorie de test
        $this->category = BoxCategory::factory()->create([
            'short_name' => 'test-category',
            'description' => 'Catégorie de test'
        ]);
    }

    /** @test */
    public function user_can_get_list_of_active_boxes()
    {
        // Créer des boîtes actives et inactives
        $activeBox = Box::factory()->create([
            'active' => true,
            'box_category_id' => $this->category->id
        ]);

        $inactiveBox = Box::factory()->create([
            'active' => false,
            'box_category_id' => $this->category->id
        ]);

        $response = $this->getJson('/api/boxes');

        $response->assertStatus(200)
            ->assertJsonCount(1) // Seule la boîte active
            ->assertJsonFragment(['id' => $activeBox->id])
            ->assertJsonMissing(['id' => $inactiveBox->id]);
    }

    /** @test */
    public function user_can_get_box_details()
    {
        $box = Box::factory()->create([
            'box_category_id' => $this->category->id
        ]);

        $response = $this->getJson("/api/boxes/{$box->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'id',
                'name',
                'description',
                'base_price',
                'active',
                'box_category_id',
                'category',
                'items'
            ])
            ->assertJson([
                'id' => $box->id,
                'name' => $box->name,
                'description' => $box->description,
                'base_price' => $box->base_price,
            ]);
    }

    /** @test */
    public function user_gets_404_for_nonexistent_box()
    {
        $response = $this->getJson('/api/boxes/999');

        $response->assertStatus(404);
    }

    /** @test */
    public function admin_can_get_all_boxes_including_inactive()
    {
        $admin = $this->createAdmin();
        $token = $this->getJWTToken($admin);

        $activeBox = Box::factory()->create([
            'active' => true,
            'box_category_id' => $this->category->id
        ]);

        $inactiveBox = Box::factory()->create([
            'active' => false,
            'box_category_id' => $this->category->id
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/admin/boxes');

        $response->assertStatus(200)
            ->assertJsonCount(2) // Les deux boîtes
            ->assertJsonFragment(['id' => $activeBox->id])
            ->assertJsonFragment(['id' => $inactiveBox->id]);
    }

    /** @test */
    public function non_admin_cannot_access_admin_boxes_endpoint()
    {
        $user = $this->createUser(['role' => 'user']);
        $token = $this->getJWTToken($user);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/admin/boxes');

        $response->assertStatus(403);
    }

    /** @test */
    public function admin_can_update_box()
    {
        $admin = $this->createAdmin();
        $token = $this->getJWTToken($admin);

        $box = Box::factory()->create([
            'box_category_id' => $this->category->id
        ]);

        $updateData = [
            'name' => 'Boîte Mise à Jour',
            'description' => 'Description mise à jour',
            'base_price' => 29.99,
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->putJson("/api/admin/boxes/{$box->id}", $updateData);

        $response->assertStatus(200)
            ->assertJson(['message' => 'Box updated successfully']);

        $this->assertDatabaseHas('boxes', [
            'id' => $box->id,
            'name' => $updateData['name'],
            'description' => $updateData['description'],
            'base_price' => $updateData['base_price'],
        ]);
    }

    /** @test */
    public function non_admin_cannot_update_box()
    {
        $user = $this->createUser(['role' => 'user']);
        $token = $this->getJWTToken($user);

        $box = Box::factory()->create([
            'box_category_id' => $this->category->id
        ]);

        $updateData = [
            'name' => 'Boîte Mise à Jour',
            'description' => 'Description mise à jour',
            'base_price' => 29.99,
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->putJson("/api/admin/boxes/{$box->id}", $updateData);

        $response->assertStatus(403);
    }

    /** @test */
    public function admin_cannot_update_nonexistent_box()
    {
        $admin = $this->createAdmin();
        $token = $this->getJWTToken($admin);

        $updateData = [
            'name' => 'Boîte Mise à Jour',
            'description' => 'Description mise à jour',
            'base_price' => 29.99,
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->putJson('/api/admin/boxes/999', $updateData);

        $response->assertStatus(404);
    }

    /** @test */
    public function admin_cannot_update_box_with_invalid_data()
    {
        $admin = $this->createAdmin();
        $token = $this->getJWTToken($admin);

        $box = Box::factory()->create([
            'box_category_id' => $this->category->id
        ]);

        $updateData = [
            'name' => '', // Nom requis
            'base_price' => -10, // Prix négatif
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->putJson("/api/admin/boxes/{$box->id}", $updateData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'base_price']);
    }
}
