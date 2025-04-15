<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Object>
 */
class ItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->word(),
            'description' => $this->faker->sentence(),
            'image_url' => $this->faker->imageUrl(),
            'created_at' => now(),
            'updated_at' => now(),
            'weight' => $this->faker->randomFloat(2, 0, 100),
            'quantity' => $this->faker->numberBetween(1, 100),
        ];
    }
}
