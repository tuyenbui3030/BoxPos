<?php

namespace Packages\MaterialCatalog\Database\Seeders;

use Illuminate\Database\Seeder;

class MaterialCatalogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call([
            MaterialUnitsSeeder::class,
            MaterialCategoriesSeeder::class,
        ]);
    }
}
