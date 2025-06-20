<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\GiftCardType;

class GiftCardTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        GiftCardType::insert([
            [
                'name' => '1 BOX',
                'description' => 'Box mystère, box activité manuelle ou box DIY',
                'base_price' => 36.90,
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'ABONNEMENT DE 3 MOIS',
                'description' => 'Abonnement de 3 mois à la box créative',
                'base_price' => 99.90,
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'ABONNEMENT DE 6 MOIS',
                'description' => 'Abonnement de 6 mois à la box créative',
                'base_price' => 189.90,
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'ABONNEMENT DE 1 AN',
                'description' => 'Abonnement de 1 an à la box créative',
                'base_price' => 359.90,
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'ABONNEMENT DE 1 ANS BOX MYSTÈRE',
                'description' => 'Abonnement de 1 ans à la box mystère',
                'base_price' => 389.90,
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
