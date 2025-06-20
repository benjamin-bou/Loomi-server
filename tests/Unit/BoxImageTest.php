<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Box;
use App\Models\BoxImage;
use App\Models\BoxCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;

class BoxImageTest extends TestCase
{
    use RefreshDatabase;

    private function createTestBox($category = null, $overrides = [])
    {
        if (!$category) {
            $category = BoxCategory::factory()->create();
        }

        return Box::create(array_merge([
            'name' => 'Test Box',
            'description' => 'Test description',
            'base_price' => 29.90,
            'active' => true,
            'quantity' => 10,
            'available_from' => now(),
            'box_category_id' => $category->id
        ], $overrides));
    }

    #[Test]
    public function box_image_belongs_to_box()
    {
        $category = BoxCategory::factory()->create();
        $box = $this->createTestBox($category);
        $boxImage = BoxImage::factory()->create(['box_id' => $box->id]);

        $this->assertInstanceOf(Box::class, $boxImage->box);
        $this->assertEquals($box->id, $boxImage->box->id);
    }

    #[Test]
    public function box_has_many_images()
    {
        $category = BoxCategory::factory()->create();
        $box = $this->createTestBox($category);
        $images = BoxImage::factory()->count(3)->create(['box_id' => $box->id]);

        $this->assertInstanceOf('Illuminate\Database\Eloquent\Collection', $box->images);
        $this->assertCount(3, $box->images);
        $this->assertInstanceOf(BoxImage::class, $box->images->first());
    }

    #[Test]
    public function box_image_fillable_attributes_are_correct()
    {
        $fillable = [
            'boxes_images_id',
            'publication_date',
            'link',
            'alt',
            'box_id',
        ];

        $boxImage = new BoxImage();

        $this->assertEquals($fillable, $boxImage->getFillable());
    }

    #[Test]
    public function box_image_publication_date_is_cast_to_datetime()
    {
        $category = BoxCategory::factory()->create();
        $box = $this->createTestBox($category);
        $boxImage = BoxImage::factory()->create([
            'box_id' => $box->id,
            'publication_date' => '2023-12-25 14:30:00'
        ]);

        $this->assertInstanceOf('Carbon\Carbon', $boxImage->publication_date);
        $this->assertEquals('2023-12-25 14:30:00', $boxImage->publication_date->format('Y-m-d H:i:s'));
    }

    #[Test]
    public function box_image_boxes_images_id_is_unique()
    {
        $category = BoxCategory::factory()->create();
        $box1 = $this->createTestBox($category);
        $box2 = $this->createTestBox($category);

        $uniqueId = 'unique_image_001';

        BoxImage::factory()->create([
            'box_id' => $box1->id,
            'boxes_images_id' => $uniqueId
        ]);

        // Tenter de créer une autre image avec le même boxes_images_id devrait échouer
        $this->expectException(\Illuminate\Database\QueryException::class);

        BoxImage::factory()->create([
            'box_id' => $box2->id,
            'boxes_images_id' => $uniqueId
        ]);
    }

    #[Test]
    public function box_image_can_be_created_with_all_attributes()
    {
        $category = BoxCategory::factory()->create();
        $box = $this->createTestBox($category);

        $imageData = [
            'boxes_images_id' => 'test_image_001',
            'publication_date' => now(),
            'link' => '/images/boxes/test_box.jpg',
            'alt' => 'Image de test pour la boîte',
            'box_id' => $box->id,
        ];

        $boxImage = BoxImage::create($imageData);

        $this->assertDatabaseHas('boxes_images', $imageData);
        $this->assertEquals($imageData['boxes_images_id'], $boxImage->boxes_images_id);
        $this->assertEquals($imageData['link'], $boxImage->link);
        $this->assertEquals($imageData['alt'], $boxImage->alt);
        $this->assertEquals($box->id, $boxImage->box_id);
    }
}
