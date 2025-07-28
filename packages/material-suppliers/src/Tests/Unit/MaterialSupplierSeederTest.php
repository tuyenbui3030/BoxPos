<?php

namespace Packages\MaterialSuppliers\Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Packages\MaterialSuppliers\Database\Seeders\MaterialSupplierSeeder;
use Packages\MaterialSuppliers\Models\MaterialSupplier;
use Packages\MaterialSuppliers\Models\SupplierContact;
use Packages\Store\Models\Store;
use Packages\User\Models\User;
use Tests\TestCase;

class MaterialSupplierSeederTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create admin user
        User::factory()->create(['id' => 1]);
    }

    /** @test */
    public function it_creates_material_suppliers_for_all_stores()
    {
        // Arrange
        $stores = Store::factory()->count(3)->create(['status' => Store::STATUS_ACTIVE]);
        
        // Act
        $this->artisan('db:seed', ['--class' => MaterialSupplierSeeder::class]);
        
        // Assert
        foreach ($stores as $store) {
            $this->assertGreaterThan(0, MaterialSupplier::where('store_id', $store->id)->count());
        }
    }

    /** @test */
    public function it_creates_supplier_contacts_for_each_supplier()
    {
        // Arrange
        $store = Store::factory()->create(['status' => Store::STATUS_ACTIVE]);
        
        // Act
        $this->artisan('db:seed', ['--class' => MaterialSupplierSeeder::class]);
        
        // Assert
        $suppliers = MaterialSupplier::where('store_id', $store->id)->get();
        
        foreach ($suppliers as $supplier) {
            $this->assertGreaterThan(0, $supplier->contacts()->count());
            
            // Each supplier should have at least one primary contact
            $this->assertTrue($supplier->contacts()->where('is_primary', true)->exists());
        }
    }

    /** @test */
    public function it_ensures_suppliers_are_distributed_evenly_between_stores()
    {
        // Arrange
        $stores = Store::factory()->count(3)->create(['status' => Store::STATUS_ACTIVE]);
        
        // Act
        $this->artisan('db:seed', ['--class' => MaterialSupplierSeeder::class]);
        
        // Assert
        $supplierCounts = [];
        foreach ($stores as $store) {
            $supplierCounts[] = MaterialSupplier::where('store_id', $store->id)->count();
        }
        
        // All stores should have suppliers
        foreach ($supplierCounts as $count) {
            $this->assertGreaterThan(0, $count);
        }
        
        // The difference between max and min should not be too large (within reasonable range)
        $maxCount = max($supplierCounts);
        $minCount = min($supplierCounts);
        $this->assertLessThanOrEqual(5, $maxCount - $minCount, 'Suppliers should be distributed relatively evenly');
    }

    /** @test */
    public function it_creates_standard_suppliers_with_vietnamese_data()
    {
        // Arrange
        $store = Store::factory()->create(['status' => Store::STATUS_ACTIVE]);
        
        // Act
        $this->artisan('db:seed', ['--class' => MaterialSupplierSeeder::class]);
        
        // Assert
        $suppliers = MaterialSupplier::where('store_id', $store->id)->get();
        
        // Should have standard suppliers with Vietnamese company names
        $this->assertTrue($suppliers->contains('company_name', 'Công ty Xi măng Hà Tiên'));
        $this->assertTrue($suppliers->contains('company_name', 'Tập đoàn Hòa Phát'));
        $this->assertTrue($suppliers->contains('company_name', 'Công ty Gạch Đồng Tâm'));
        
        // All suppliers should have store_id set
        foreach ($suppliers as $supplier) {
            $this->assertEquals($store->id, $supplier->store_id);
            $this->assertEquals('Vietnam', $supplier->country);
            $this->assertTrue($supplier->is_active);
        }
    }

    /** @test */
    public function it_maintains_store_isolation()
    {
        // Arrange
        $store1 = Store::factory()->create(['status' => Store::STATUS_ACTIVE]);
        $store2 = Store::factory()->create(['status' => Store::STATUS_ACTIVE]);
        
        // Act
        $this->artisan('db:seed', ['--class' => MaterialSupplierSeeder::class]);
        
        // Assert
        $store1Suppliers = MaterialSupplier::where('store_id', $store1->id)->get();
        $store2Suppliers = MaterialSupplier::where('store_id', $store2->id)->get();
        
        // Ensure no cross-store data leakage
        foreach ($store1Suppliers as $supplier) {
            $this->assertEquals($store1->id, $supplier->store_id);
            
            // Check contacts also maintain store isolation through supplier
            foreach ($supplier->contacts as $contact) {
                $this->assertEquals($store1->id, $contact->supplier->store_id);
            }
        }
        
        foreach ($store2Suppliers as $supplier) {
            $this->assertEquals($store2->id, $supplier->store_id);
            
            // Check contacts also maintain store isolation through supplier
            foreach ($supplier->contacts as $contact) {
                $this->assertEquals($store2->id, $contact->supplier->store_id);
            }
        }
    }

    /** @test */
    public function it_creates_contacts_with_full_information()
    {
        // Arrange
        $store = Store::factory()->create(['status' => Store::STATUS_ACTIVE]);
        
        // Act
        $this->artisan('db:seed', ['--class' => MaterialSupplierSeeder::class]);
        
        // Assert
        $contacts = SupplierContact::whereHas('supplier', function ($query) use ($store) {
            $query->where('store_id', $store->id);
        })->get();
        
        foreach ($contacts as $contact) {
            // Each contact should have required information
            $this->assertNotEmpty($contact->name);
            $this->assertNotEmpty($contact->position);
            $this->assertNotEmpty($contact->department);
            $this->assertTrue($contact->phone || $contact->mobile); // At least one phone number
            $this->assertNotEmpty($contact->email);
            $this->assertTrue($contact->is_active);
            
            // Should have responsibilities
            $this->assertIsArray($contact->responsibilities);
            $this->assertNotEmpty($contact->responsibilities);
        }
    }
}