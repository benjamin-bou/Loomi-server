<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\BoxCategory;

class BoxCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['short_name' => 'DIY', 'description' => 'Do It Yourself : activités créatives à faire soi-même'],
            ['short_name' => 'Activité manuelle', 'description' => 'Activités manuelles pour tous les âges'],
        ];

        foreach ($categories as $cat) {
            BoxCategory::firstOrCreate(['short_name' => $cat['short_name']], $cat);
        }
    }
}
