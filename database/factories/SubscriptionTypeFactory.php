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
     */    public function definition(): array
    {
        return [
            'label' => fake()->randomElement(['Abonnement Mensuel', 'Abonnement Trimestriel', 'Abonnement Annuel']),
            'description' => fake()->sentence(),
            'price' => fake()->randomFloat(2, 20, 200),
            'recurrence' => fake()->randomElement(['monthly', 'quarterly', 'yearly']),
        ];
    }
}
