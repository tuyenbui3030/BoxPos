<?php

namespace Database\Seeders;

use Packages\User\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     * Chạy tất cả seeders theo thứ tự đúng (dependencies)
     */
    public function run(): void
    {
        $this->command->info('🌱 Starting database seeding...');

        $this->call([
            // 1. Stores phải tạo trước (base dependency)
            StoreSeeder::class,

            // 2. Users (depends on stores)
            UserSeeder::class,

            // 3. Products & Categories (depends on stores)
            ProductSeeder::class,

            // 4. Other seeders from packages (if exist)
            // \Packages\Store\Database\Seeders\StoreSeeder::class,
            // \Packages\User\Database\Seeders\UserSeeder::class,
            // \Packages\Customer\Database\Seeders\CustomerSeeder::class,
        ]);

        $this->command->info('✅ Database seeding completed successfully!');
        $this->command->info('📧 Login: admin@demo-coffee.com / password');
    }
}
