<?php

namespace Database\Seeders;

use App\Models\User;
use Database\Seeders\ItemSeeder;
use Database\Seeders\BoxItemSeeder;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            BoxCategorySeeder::class,
            ItemSeeder::class, // Créer les items avant les boîtes
            BoxSeeder::class,
            BoxImageSeeder::class, // Nouveau seeder pour les images
            BoxItemSeeder::class, // Après avoir créé les boîtes et les items
            GiftCardSeeder::class,
            PaymentMethodTypeSeeder::class,
            OrderSeeder::class,
            SubscriptionTypeSeeder::class,
        ]);
    }
}
