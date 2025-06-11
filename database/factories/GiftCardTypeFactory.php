<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\GiftCardType>
 */
class GiftCardTypeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => 'Carte Cadeau ' . fake()->numberBetween(25, 100) . '€',
            'description' => fake()->sentence(),
            'base_price' => fake()->randomFloat(2, 25, 100),
            'active' => true,
        ];
    }
}
