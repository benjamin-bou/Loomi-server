<?php

namespace Database\Seeders;

use App\Models\GiftCard;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Order;
use App\Models\BoxOrder;
use App\Models\PaymentMethod;
use App\Models\PaymentMethodType;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        BoxOrder::factory()->count(20)->create();

        Order::factory()->count(20)->create()->each(function ($order) {
            $case = rand(1, 3); // Tirage aléatoire du scénario

            switch ($case) {
                case 1:
                    // Cas 1 : Paiement direct unique
                    PaymentMethod::factory()->create([
                        'order_id' => $order->id,
                        'payment_method_type_id' => PaymentMethodType::where('name', '<>', 'Gift Card')->inRandomOrder()->first()->id,
                        'gift_card_id' => null,
                        'amount' => null, // plus de montant
                    ]);
                    break;

                case 2:
                    // Cas 2 : Carte cadeau uniquement (offre 1 box ou 1 abonnement)
                    $giftCard = GiftCard::inRandomOrder()->first();
                    PaymentMethod::factory()->create([
                        'order_id' => $order->id,
                        'payment_method_type_id' => PaymentMethodType::where('name', 'Gift Card')->first()->id,
                        'gift_card_id' => $giftCard ? $giftCard->id : null,
                        'amount' => null, // plus de montant
                    ]);
                    break;

                case 3:
                    // Cas 3 : Paiement direct + carte cadeau (ex: 1 box offerte + 1 box achetée)
                    $giftCard = GiftCard::inRandomOrder()->first();
                    // Paiement gift card
                    PaymentMethod::factory()->create([
                        'order_id' => $order->id,
                        'payment_method_type_id' => PaymentMethodType::where('name', 'Gift Card')->first()->id,
                        'gift_card_id' => $giftCard ? $giftCard->id : null,
                        'amount' => null, // plus de montant
                    ]);
                    // Paiement complémentaire
                    PaymentMethod::factory()->create([
                        'order_id' => $order->id,
                        'payment_method_type_id' => PaymentMethodType::where('name', '<>', 'Gift Card')->inRandomOrder()->first()->id,
                        'gift_card_id' => null,
                        'amount' => null, // plus de montant
                    ]);
                    break;
            }
        });
    }
}
