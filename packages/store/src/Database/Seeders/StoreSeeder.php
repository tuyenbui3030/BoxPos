<?php

namespace Packages\Store\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StoreSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('stores')->insert([
            [
                'name' => 'Quản Lý Vật Liệu Xây Dựng',
                'slug' => 'vat-lieu-xay-dung',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Quản Lý Cà Phê',
                'slug' => 'quan-ly-ca-phe',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
