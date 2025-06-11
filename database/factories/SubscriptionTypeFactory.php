<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SubscriptionType>
 */
class SubscriptionTypeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->randomElement(['Abonnement Mensuel', 'Abonnement Trimestriel', 'Abonnement Annuel']),
            'description' => fake()->sentence(),
            'duration_months' => fake()->randomElement([1, 3, 12]),
            'price' => fake()->randomFloat(2, 20, 200),
            'active' => true,
        ];
    }
}
