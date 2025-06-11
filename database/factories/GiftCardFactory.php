<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\GiftCard>
 */
class GiftCardFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => strtoupper($this->faker->unique()->bothify('???-###-???')),
            'expiration_date' => $this->faker->optional()->dateTimeBetween('now', '+1 year'),
            'used_at' => null,
            'gift_card_type_id' => \App\Models\GiftCardType::factory(),
            'order_id' => null,
            'activated_by' => null,
        ];
    }
}
