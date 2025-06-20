<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Box;
use App\Models\BoxCategory;
use App\Models\BoxImage;
use PHPUnit\Framework\Attributes\Test;

class BoxApiTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function can_get_all_boxes_with_images()
    {
        // Créer une catégorie
        $category = BoxCategory::factory()->create(['short_name' => 'Activité manuelle']);

        // Créer une boîte
        $box = Box::create([
            'name' => 'Box Test',
            'description' => 'Box de test API',
            'base_price' => 32.90,
            'active' => true,
            'quantity' => 15,
            'available_from' => now(),
            'box_category_id' => $category->id
        ]);

        // Créer des images pour cette boîte
        BoxImage::factory()->count(2)->create(['box_id' => $box->id]);

        // Appeler l'API
        $response = $this->getJson('/api/boxes');

        $response->assertStatus(200)
            ->assertJsonStructure([
                '*' => [
                    'id',
                    'name',
                    'description',
                    'base_price',
                    'active',
                    'category' => [
                        'id',
                        'short_name'
                    ],
                    'images' => [
                        '*' => [
                            'id',
                            'boxes_images_id',
                            'publication_date',
                            'link',
                            'alt',
                            'box_id'
                        ]
                    ]
                ]
            ]);

        // Vérifier que la boîte a bien ses images
        $boxData = $response->json()[0];
        $this->assertEquals('Box Test', $boxData['name']);
        $this->assertCount(2, $boxData['images']);
    }

    #[Test]
    public function can_get_specific_box_with_images()
    {
        // Créer une catégorie
        $category = BoxCategory::factory()->create(['short_name' => 'DIY']);

        // Créer une boîte
        $box = Box::create([
            'name' => 'Box Spécifique',
            'description' => 'Box spécifique pour test',
            'base_price' => 29.90,
            'active' => true,
            'quantity' => 25,
            'available_from' => now(),
            'box_category_id' => $category->id
        ]);

        // Créer une image pour cette boîte
        BoxImage::factory()->create([
            'box_id' => $box->id,
            'boxes_images_id' => 'test_box_001',
            'alt' => 'Image de test'
        ]);

        // Appeler l'API pour une boîte spécifique
        $response = $this->getJson("/api/boxes/{$box->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'id',
                'name',
                'description',
                'base_price',
                'active',
                'category' => [
                    'id',
                    'short_name'
                ],
                'images' => [
                    '*' => [
                        'id',
                        'boxes_images_id',
                        'link',
                        'alt'
                    ]
                ]
            ]);

        $boxData = $response->json();
        $this->assertEquals('Box Spécifique', $boxData['name']);
        $this->assertCount(1, $boxData['images']);
        $this->assertEquals('test_box_001', $boxData['images'][0]['boxes_images_id']);
    }

    #[Test]
    public function boxes_api_only_returns_active_boxes()
    {
        // Créer une catégorie
        $category = BoxCategory::factory()->create();

        // Créer une boîte active et une inactive
        $activeBox = Box::create([
            'name' => 'Box Active',
            'description' => 'Box active pour test',
            'base_price' => 25.90,
            'active' => true,
            'quantity' => 10,
            'available_from' => now(),
            'box_category_id' => $category->id
        ]);

        $inactiveBox = Box::create([
            'name' => 'Box Inactive',
            'description' => 'Box inactive pour test',
            'base_price' => 30.90,
            'active' => false,
            'quantity' => 5,
            'available_from' => now(),
            'box_category_id' => $category->id
        ]);

        // Appeler l'API
        $response = $this->getJson('/api/boxes');

        $response->assertStatus(200);

        $boxes = $response->json();
        $this->assertCount(1, $boxes);
        $this->assertEquals($activeBox->id, $boxes[0]['id']);
    }
}
