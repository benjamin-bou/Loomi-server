<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Box;
use App\Models\BoxCategory;

class BoxSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = BoxCategory::all();
        Box::factory()->count(10)->create()->each(function ($box) use ($categories) {
            $box->base_price = (mt_rand(0, 1) === 0) ? 19.99 : 29.99;
            // Attribution d'une catégorie aléatoire
            $box->box_category_id = $categories->random()->id;
            $box->save();
        });
    }
}
