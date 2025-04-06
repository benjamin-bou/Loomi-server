<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Order;
use App\Models\PaymentMethod;
use App\Models\PaymentMethodType;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Order::factory()->count(20)->create()->each(function ($order) {
            $case = rand(1, 3); // Tirage aléatoire du scénario

            switch ($case) {
                case 1:
                    // Cas 1 : Paiement direct unique
                    PaymentMethod::factory()->create([
                        'order_id' => $order->id,
                        'payment_method_type_id' => PaymentMethodType::where('name', '<>', 'Gift Card')->inRandomOrder()->first()->id,
                        'gift_card_id' => null,
                        'amount' => $order->total_amount,
                    ]);
                    break;

                case 2:
                    // Cas 2 : Carte cadeau uniquement
                    PaymentMethod::factory()->create([
                        'order_id' => $order->id,
                        'payment_method_type_id' => PaymentMethodType::where('name', 'Gift Card')->first()->id,
                        'gift_card_id' => null,
                        // 'gift_card_id' => GiftCard::inRandomOrder()->first()->id,
                        'amount' => $order->total_amount,
                    ]);
                    break;

                case 3:
                    // Cas 3 : Carte cadeau partielle + paiement direct complémentaire
                    $giftAmount = fake()->randomFloat(2, 5, $order->total_amount - 1); // partielle
                    $remaining = $order->total_amount - $giftAmount;

                    // Paiement gift card
                    PaymentMethod::factory()->create([
                        'order_id' => $order->id,
                        'payment_method_type_id' => PaymentMethodType::where('name', 'Gift Card')->first()->id,
                        'gift_card_id' => null,
                        // 'gift_card_id' => GiftCard::inRandomOrder()->first()->id,
                        'amount' => $giftAmount,
                    ]);

                    // Paiement complémentaire
                    PaymentMethod::factory()->create([
                        'order_id' => $order->id,
                        'payment_method_type_id' => PaymentMethodType::where('name', '<>', 'Gift Card')->inRandomOrder()->first()->id,
                        'gift_card_id' => null,
                        'amount' => $remaining,
                    ]);
                    break;
            }
        });
    }
}
