<?php

namespace Database\Seeders;

use App\Models\PaymentMethodType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PaymentMethodTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        PaymentMethodType::insert([
            ['name' => 'Credit Card'],
            ['name' => 'PayPal'],
            ['name' => 'Apple Pay'],
            ['name' => 'Google Pay'],
            ['name' => 'Samsung Pay'],
            ['name' => 'Gift Card'],
        ]);
    }
}
