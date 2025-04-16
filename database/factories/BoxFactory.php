<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Box>
 */
class BoxFactory extends Factory
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
            'base_price' => $this->faker->randomFloat(2, 10, 100),
            'active' => $this->faker->boolean(),
            'quantity' => $this->faker->numberBetween(0, 1000),
            'available_from' => now(),
        ];
    }
}
