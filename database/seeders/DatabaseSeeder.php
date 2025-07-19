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
            \Packages\Store\Database\Seeders\StoreSeeder::class,
            \Packages\User\Database\Seeders\UserSeeder::class,
            \Packages\User\Database\Seeders\UserStoreSeeder::class, // Assign users to stores
            \Packages\Customer\Database\Seeders\CustomerSeeder::class,
            // Add more seeders here as needed
        ]);
    }
}
