<?php

namespace Packages\Customer\Tests\Feature;

use Packages\Customer\Models\Customer;
use Packages\Customer\Http\Requests\StoreCustomerRequest;
use Packages\Customer\Http\Requests\UpdateCustomerRequest;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * Customer API Test
 * 
 * Feature tests for Customer API endpoints.
 */
class CustomerApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Setup test data or authentication if needed
    }

    public function test_can_create_customer(): void
    {
        // Arrange
        $customerData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '+1234567890',
            'status' => 'active'
        ];

        // Act
        $response = $this->postJson('/api/customers', $customerData);

        // Assert
        $response->assertStatus(201)
                 ->assertJsonStructure([
                     'data' => [
                         'id',
                         'name',
                         'email',
                         'phone',
                         'status',
                         'created_at',
                         'updated_at'
                     ]
                 ]);

        $this->assertDatabaseHas('customers', [
            'name' => 'John Doe',
            'email' => 'john@example.com'
        ]);
    }

    public function test_can_get_customer(): void
    {
        // Arrange
        $customer = Customer::factory()->create([
            'name' => 'Jane Doe',
            'email' => 'jane@example.com'
        ]);

        // Act
        $response = $this->getJson("/api/customers/{$customer->id}");

        // Assert
        $response->assertStatus(200)
                 ->assertJson([
                     'data' => [
                         'id' => $customer->id,
                         'name' => 'Jane Doe',
                         'email' => 'jane@example.com'
                     ]
                 ]);
    }

    public function test_can_update_customer(): void
    {
        // Arrange
        $customer = Customer::factory()->create([
            'name' => 'Original Name',
            'email' => 'original@example.com'
        ]);

        $updateData = [
            'name' => 'Updated Name',
            'phone' => '+9876543210'
        ];

        // Act
        $response = $this->putJson("/api/customers/{$customer->id}", $updateData);

        // Assert
        $response->assertStatus(200)
                 ->assertJson([
                     'data' => [
                         'id' => $customer->id,
                         'name' => 'Updated Name',
                         'phone' => '+9876543210'
                     ]
                 ]);

        $this->assertDatabaseHas('customers', [
            'id' => $customer->id,
            'name' => 'Updated Name'
        ]);
    }

    public function test_can_delete_customer(): void
    {
        // Arrange
        $customer = Customer::factory()->create();

        // Act
        $response = $this->deleteJson("/api/customers/{$customer->id}");

        // Assert
        $response->assertStatus(204);
        $this->assertSoftDeleted('customers', ['id' => $customer->id]);
    }

    public function test_returns_404_for_non_existent_customer(): void
    {
        // Act
        $response = $this->getJson('/api/customers/999');

        // Assert
        $response->assertStatus(404);
    }

    public function test_validates_required_fields_on_create(): void
    {
        // Act
        $response = $this->postJson('/api/customers', []);

        // Assert
        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['name', 'email']);
    }

    public function test_validates_email_uniqueness_on_create(): void
    {
        // Arrange
        $existingCustomer = Customer::factory()->create([
            'email' => 'existing@example.com'
        ]);

        $customerData = [
            'name' => 'New Customer',
            'email' => 'existing@example.com'
        ];

        // Act
        $response = $this->postJson('/api/customers', $customerData);

        // Assert
        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['email']);
    }
}
