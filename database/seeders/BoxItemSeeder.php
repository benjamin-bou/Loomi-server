<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Box;
use App\Models\Item;
use Illuminate\Support\Facades\DB;

class BoxItemSeeder extends Seeder
{
    public function run(): void
    {
        $boxes = Box::all();
        $items = Item::all();

        foreach ($boxes as $box) {
            $randomItems = $items->random(rand(3, 5)); // Select 1 to 5 random items

            foreach ($randomItems as $item) {
                DB::table('box_item')->insert([
                    'box_id' => $box->id,
                    'item_id' => $item->id,
                    'quantity' => rand(1, 10), // Random quantity between 1 and 10
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
