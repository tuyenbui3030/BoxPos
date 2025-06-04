<?php

namespace Packages\Customer\Tests\Unit;

use Packages\Customer\Models\Customer;
use Packages\Customer\Services\CustomerService;
use Packages\Customer\Repositories\CustomerRepository;
use Packages\Customer\Exceptions\CustomerNotFoundException;
use PHPUnit\Framework\TestCase;
use Mockery;

/**
 * Customer Service Test
 * 
 * Unit tests for CustomerService class.
 */
class CustomerServiceTest extends TestCase
{
    protected CustomerService $customerService;
    protected $customerRepository;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->customerRepository = Mockery::mock(CustomerRepository::class);
        $this->customerService = new CustomerService($this->customerRepository);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_get_customer_by_id_returns_customer_when_found(): void
    {
        // Arrange
        $customerId = 1;
        $customer = new Customer(['id' => $customerId, 'name' => 'John Doe']);
        
        $this->customerRepository
            ->shouldReceive('findById')
            ->with($customerId)
            ->once()
            ->andReturn($customer);

        // Act
        $result = $this->customerService->getCustomerById($customerId);

        // Assert
        $this->assertInstanceOf(Customer::class, $result);
        $this->assertEquals($customerId, $result->id);
    }

    public function test_get_customer_by_id_throws_exception_when_not_found(): void
    {
        // Arrange
        $customerId = 999;
        
        $this->customerRepository
            ->shouldReceive('findById')
            ->with($customerId)
            ->once()
            ->andReturn(null);

        // Assert
        $this->expectException(CustomerNotFoundException::class);
        $this->expectExceptionMessage("Customer with ID {$customerId} not found");

        // Act
        $this->customerService->getCustomerById($customerId);
    }

    public function test_create_customer_returns_created_customer(): void
    {
        // Arrange
        $customerData = [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'status' => 'active'
        ];
        $createdCustomer = new Customer($customerData);
        
        $this->customerRepository
            ->shouldReceive('create')
            ->with($customerData)
            ->once()
            ->andReturn($createdCustomer);

        // Act
        $result = $this->customerService->createCustomer($customerData);

        // Assert
        $this->assertInstanceOf(Customer::class, $result);
        $this->assertEquals($customerData['name'], $result->name);
        $this->assertEquals($customerData['email'], $result->email);
    }

    public function test_update_customer_returns_updated_customer(): void
    {
        // Arrange
        $customerId = 1;
        $customerData = ['name' => 'Updated Name'];
        $existingCustomer = new Customer(['id' => $customerId, 'name' => 'Old Name']);
        $updatedCustomer = new Customer(['id' => $customerId, 'name' => 'Updated Name']);
        
        $this->customerRepository
            ->shouldReceive('findById')
            ->with($customerId)
            ->once()
            ->andReturn($existingCustomer);
            
        $this->customerRepository
            ->shouldReceive('update')
            ->with($existingCustomer, $customerData)
            ->once()
            ->andReturn($updatedCustomer);

        // Act
        $result = $this->customerService->updateCustomer($customerId, $customerData);

        // Assert
        $this->assertInstanceOf(Customer::class, $result);
        $this->assertEquals('Updated Name', $result->name);
    }

    public function test_delete_customer_returns_true_when_successful(): void
    {
        // Arrange
        $customerId = 1;
        $customer = new Customer(['id' => $customerId, 'name' => 'John Doe']);
        
        $this->customerRepository
            ->shouldReceive('findById')
            ->with($customerId)
            ->once()
            ->andReturn($customer);
            
        $this->customerRepository
            ->shouldReceive('delete')
            ->with($customer)
            ->once()
            ->andReturn(true);

        // Act
        $result = $this->customerService->deleteCustomer($customerId);

        // Assert
        $this->assertTrue($result);
    }
}
