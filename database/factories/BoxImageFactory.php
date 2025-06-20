<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Box;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\BoxImage>
 */
class BoxImageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'boxes_images_id' => $this->faker->unique()->uuid(),
            'publication_date' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'link' => $this->faker->imageUrl(400, 300, 'boxes', true, 'Box'),
            'alt' => $this->faker->sentence(3),
            'box_id' => Box::factory(),
        ];
    }
}
