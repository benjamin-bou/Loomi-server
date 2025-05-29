<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::factory()->count(5)->create();


        User::factory()->create([
            'last_name' => 'Test',
            'first_name' => 'User',
            'email' => 'user@example.com',
            'password' => bcrypt('password'),
            'role' => 'user',
            'address' => '123 Test St',
            'zipcode' => '12345',
            'city' => 'Test City',
        ]);


        User::create([
            'last_name' => 'Admin',
            'first_name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'address' => '123 Admin St',
            'zipcode' => '12345',
            'city' => 'Admin City',
        ]);
    }
}
