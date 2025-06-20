<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Box;
use App\Models\BoxCategory;
use App\Models\Review;
use App\Models\Item;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;

class BoxModelTest extends TestCase
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
    public function box_belongs_to_category()
    {
        $category = BoxCategory::factory()->create();
        $box = $this->createTestBox($category);

        $this->assertInstanceOf(BoxCategory::class, $box->category);
        $this->assertEquals($category->id, $box->category->id);
    }

    #[Test]
    public function box_has_many_reviews()
    {
        $box = $this->createTestBox();
        $reviews = Review::factory()->count(3)->create(['box_id' => $box->id]);

        $this->assertInstanceOf('Illuminate\Database\Eloquent\Collection', $box->reviews);
        $this->assertCount(3, $box->reviews);
        $this->assertInstanceOf(Review::class, $box->reviews->first());
    }

    #[Test]
    public function box_can_calculate_average_rating()
    {
        $box = $this->createTestBox();

        // Créer des avis avec différentes notes
        Review::factory()->create(['box_id' => $box->id, 'rating' => 5.0]);
        Review::factory()->create(['box_id' => $box->id, 'rating' => 4.0]);
        Review::factory()->create(['box_id' => $box->id, 'rating' => 3.0]);

        $averageRating = $box->averageRating();

        $this->assertEquals(4.0, $averageRating);
    }

    #[Test]
    public function box_average_rating_returns_null_when_no_reviews()
    {
        $box = $this->createTestBox();

        $averageRating = $box->averageRating();

        $this->assertNull($averageRating);
    }

    #[Test]
    public function box_can_count_total_reviews()
    {
        $box = $this->createTestBox();
        Review::factory()->count(5)->create(['box_id' => $box->id]);

        $totalReviews = $box->totalReviews();

        $this->assertEquals(5, $totalReviews);
    }

    #[Test]
    public function box_fillable_attributes_are_correct()
    {
        $fillable = [
            'name',
            'description',
            'base_price',
            'active',
            'box_category_id',
        ];

        $box = new Box();

        $this->assertEquals($fillable, $box->getFillable());
    }

    #[Test]
    public function box_belongs_to_many_items()
    {
        $box = $this->createTestBox();
        $items = Item::factory()->count(3)->create();

        // Attacher les items à la boîte avec des quantités
        foreach ($items as $index => $item) {
            $box->items()->attach($item->id, ['quantity' => $index + 1]);
        }

        $this->assertInstanceOf('Illuminate\Database\Eloquent\Collection', $box->items);
        $this->assertCount(3, $box->items);
        $this->assertInstanceOf(Item::class, $box->items->first());

        // Vérifier que les quantités sont bien récupérées
        $this->assertEquals(1, $box->items->first()->pivot->quantity);
    }

    #[Test]
    public function box_scope_active_filters_correctly()
    {
        $activeBox = $this->createTestBox(null, ['active' => true]);
        $inactiveBox = $this->createTestBox(null, ['active' => false]);

        $activeBoxes = Box::where('active', true)->get();

        $this->assertCount(1, $activeBoxes);
        $this->assertEquals($activeBox->id, $activeBoxes->first()->id);
    }

    #[Test]
    public function box_price_is_stored_as_decimal()
    {
        $box = $this->createTestBox(null, ['base_price' => 19.99]);

        $this->assertEquals(19.99, $box->base_price);
        $this->assertIsFloat($box->base_price);
    }
}
