<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Order;
use App\Models\PaymentMethodType;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PaymentMethod>
 */
class PaymentMethodFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'payment_method_type_id' => PaymentMethodType::inRandomOrder()->first()->id,
            // 'gift_card_id' => 
            'amount' => $this->faker->randomFloat(2, 10, 1000),
            'order_id' => Order::inRandomOrder()->first()->id,
        ];
    }
}
