<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Subscription>
 */
class SubscriptionFactory extends Factory
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
            'duration' => $this->faker->randomElement([1, 3, 6, 12]),
            'base_price' => $this->faker->randomFloat(2, 10, 100),
            'active' => true,
            'renouvellement' => $this->faker->boolean(),
            'subscription_type_id' => 1, // à adapter selon vos seeds
        ];
    }
}
