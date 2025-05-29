<?php

namespace Database\Seeders;

use Packages\User\Models\User;
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
            \Packages\User\Database\Seeders\UserSeeder::class,
            \Packages\Customer\Database\Seeders\CustomerSeeder::class,
            // Add more seeders here as needed
        ]);
    }
}
