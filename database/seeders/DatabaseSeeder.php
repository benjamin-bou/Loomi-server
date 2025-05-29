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
            BoxItemSeeder::class,
            BoxCategorySeeder::class,
            BoxSeeder::class,
            GiftCardSeeder::class,
            PaymentMethodTypeSeeder::class,
            OrderSeeder::class,
            ItemSeeder::class,
            SubscriptionTypeSeeder::class,
        ]);
    }
}
