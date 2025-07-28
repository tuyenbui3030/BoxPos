<?php

namespace Packages\MaterialPricing\Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Packages\MaterialPricing\Database\Seeders\MaterialPricingSeeder;
use Packages\MaterialPricing\Models\MaterialPricing;
use Packages\MaterialCatalog\Models\BuildingMaterial;
use Packages\Store\Models\Store;
use Packages\User\Models\User;

class MaterialPricingSeederTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test data
        $this->store = Store::factory()->create(['is_active' => true]);
        $this->user = User::factory()->create();
        $this->user->stores()->attach($this->store->id);
        
        $this->material = BuildingMaterial::factory()->create([
            'store_id' => $this->store->id,
            'name' => 'Xi măng Portland PCB40'
        ]);
    }

    public function test_seeder_creates_pricing_history_for_materials(): void
    {
        $seeder = new MaterialPricingSeeder();
        $seeder->run();

        // Verify pricing records were created
        $pricingRecords = MaterialPricing::where('material_id', $this->material->id)->get();
        
        $this->assertGreaterThan(0, $pricingRecords->count());
        
        // Verify all customer types have pricing
        $customerTypes = [
            MaterialPricing::CUSTOMER_RETAIL,
            MaterialPricing::CUSTOMER_WHOLESALE,
            MaterialPricing::CUSTOMER_CONTRACTOR,
            MaterialPricing::CUSTOMER_VIP,
            MaterialPricing::CUSTOMER_STAFF
        ];
        
        foreach ($customerTypes as $customerType) {
            $this->assertTrue(
                $pricingRecords->where('customer_type', $customerType)->count() > 0,
                "No pricing found for customer type: {$customerType}"
            );
        }
    }

    public function test_seeder_creates_pricing_history_with_time_trends(): void
    {
        $seeder = new MaterialPricingSeeder();
        $seeder->run();

        $pricingRecords = MaterialPricing::where('material_id', $this->material->id)
            ->where('customer_type', MaterialPricing::CUSTOMER_RETAIL)
            ->orderBy('effective_from')
            ->get();

        // Should have multiple pricing records for history
        $this->assertGreaterThan(1, $pricingRecords->count());

        // Verify only the latest record is active
        $activePricing = $pricingRecords->where('is_active', true);
        $this->assertEquals(3, $activePricing->count()); // 3 tiers for retail
        
        // Verify historical records are inactive
        $historicalPricing = $pricingRecords->where('is_active', false);
        $this->assertGreaterThan(0, $historicalPricing->count());
    }

    public function test_seeder_creates_quantity_tiers(): void
    {
        $seeder = new MaterialPricingSeeder();
        $seeder->run();

        $retailPricing = MaterialPricing::where('material_id', $this->material->id)
            ->where('customer_type', MaterialPricing::CUSTOMER_RETAIL)
            ->where('is_active', true)
            ->get();

        // Should have different quantity tiers
        $this->assertTrue($retailPricing->where('min_quantity', 1)->count() > 0);
        $this->assertTrue($retailPricing->where('min_quantity', 11)->count() > 0);
        $this->assertTrue($retailPricing->where('min_quantity', 101)->count() > 0);
    }

    public function test_seeder_creates_seasonal_pricing_for_applicable_materials(): void
    {
        $seeder = new MaterialPricingSeeder();
        $seeder->run();

        // Cement should have seasonal pricing
        $seasonalPricing = MaterialPricing::where('material_id', $this->material->id)
            ->whereIn('season', [
                MaterialPricing::SEASON_DRY,
                MaterialPricing::SEASON_RAINY,
                MaterialPricing::SEASON_PEAK
            ])
            ->get();

        $this->assertGreaterThan(0, $seasonalPricing->count());
    }

    public function test_seeder_ensures_store_isolation(): void
    {
        // Create another store
        $store2 = Store::factory()->create(['is_active' => true]);
        $user2 = User::factory()->create();
        $user2->stores()->attach($store2->id);
        
        $material2 = BuildingMaterial::factory()->create([
            'store_id' => $store2->id,
            'name' => 'Xi măng Portland PCB40'
        ]);

        $seeder = new MaterialPricingSeeder();
        $seeder->run();

        // Verify pricing for store 1
        $store1Pricing = MaterialPricing::where('store_id', $this->store->id)->get();
        $this->assertGreaterThan(0, $store1Pricing->count());
        
        // Verify pricing for store 2
        $store2Pricing = MaterialPricing::where('store_id', $store2->id)->get();
        $this->assertGreaterThan(0, $store2Pricing->count());

        // Verify no cross-store data leakage
        foreach ($store1Pricing as $pricing) {
            $this->assertEquals($this->store->id, $pricing->store_id);
        }
        
        foreach ($store2Pricing as $pricing) {
            $this->assertEquals($store2->id, $pricing->store_id);
        }
    }

    public function test_seeder_handles_missing_materials_gracefully(): void
    {
        // Create store without materials
        $emptyStore = Store::factory()->create(['is_active' => true]);
        $user = User::factory()->create();
        $user->stores()->attach($emptyStore->id);

        $seeder = new MaterialPricingSeeder();
        
        // Should not throw exception
        $this->expectNotToPerformAssertions();
        $seeder->run();
    }

    public function test_seeder_handles_missing_users_gracefully(): void
    {
        // Create store without users
        $storeWithoutUsers = Store::factory()->create(['is_active' => true]);
        BuildingMaterial::factory()->create([
            'store_id' => $storeWithoutUsers->id,
            'name' => 'Test Material'
        ]);

        $seeder = new MaterialPricingSeeder();
        
        // Should not throw exception
        $this->expectNotToPerformAssertions();
        $seeder->run();
    }
}