<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SubscriptionDelivery>
 */
class SubscriptionDeliveryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */    public function definition(): array
    {
        return [
            'subscription_id' => \App\Models\Subscription::factory(),
            'box_id' => \App\Models\Box::factory(),
            'delivered_at' => fake()->optional()->dateTimeBetween('-1 month', 'now'),
        ];
    }
}
