<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Box;

class BoxSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Box::factory()->count(10)->create()->each(function ($box) {
            $box->base_price = (mt_rand(0, 1) === 0) ? 19.99 : 29.99;
            $box->save();
        });
    }
}
