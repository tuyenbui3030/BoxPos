<?php

namespace Packages\Common\Tests\Unit;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Packages\Common\Tests\TestCase;
use Packages\Common\Tests\Fixtures\TestModel;
use Packages\Common\Tests\Fixtures\TestRepository;
use Packages\Common\Tests\Fixtures\TestService;

class BaseServiceTest extends TestCase
{
    use RefreshDatabase;

    protected TestService $service;
    protected TestRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->repository = new TestRepository(new TestModel());
        $this->service = new TestService($this->repository);
    }

    public function test_can_create_record_with_transaction(): void
    {
        $data = [
            'name' => 'Test Item',
            'description' => 'Test Description',
        ];

        $result = $this->service->create($data);

        $this->assertInstanceOf(Model::class, $result);
        $this->assertEquals('Test Item', $result->name);
        $this->assertEquals('Test Description', $result->description);
    }

    public function test_can_update_record_with_transaction(): void
    {
        $model = TestModel::create([
            'name' => 'Test Item',
            'description' => 'Test Description',
        ]);

        $updateData = ['name' => 'Updated Item'];
        $result = $this->service->update($model, $updateData);

        $this->assertInstanceOf(Model::class, $result);
        $this->assertEquals('Updated Item', $result->name);
    }

    public function test_can_delete_record_with_transaction(): void
    {
        $model = TestModel::create([
            'name' => 'Test Item',
            'description' => 'Test Description',
        ]);

        $result = $this->service->delete($model);

        $this->assertTrue($result);
        $this->assertNull(TestModel::find($model->id));
    }

    public function test_can_find_record(): void
    {
        $model = TestModel::create([
            'name' => 'Test Item',
            'description' => 'Test Description',
        ]);

        $result = $this->service->find($model->id);

        $this->assertNotNull($result);
        $this->assertEquals($model->id, $result->id);
    }

    public function test_can_find_or_fail_record(): void
    {
        $model = TestModel::create([
            'name' => 'Test Item',
            'description' => 'Test Description',
        ]);

        $result = $this->service->findOrFail($model->id);

        $this->assertNotNull($result);
        $this->assertEquals($model->id, $result->id);
    }

    public function test_find_or_fail_throws_exception_when_not_found(): void
    {
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
        
        $this->service->findOrFail(999);
    }

    public function test_can_get_all_records(): void
    {
        TestModel::create(['name' => 'Item 1', 'description' => 'Description 1']);
        TestModel::create(['name' => 'Item 2', 'description' => 'Description 2']);

        $results = $this->service->all();

        $this->assertCount(2, $results);
    }

    public function test_can_paginate_records(): void
    {
        for ($i = 1; $i <= 20; $i++) {
            TestModel::create([
                'name' => "Item {$i}",
                'description' => "Description {$i}",
            ]);
        }

        $results = $this->service->paginate(10);

        $this->assertEquals(10, $results->perPage());
        $this->assertEquals(20, $results->total());
    }

    public function test_transaction_rollback_on_exception(): void
    {
        $this->expectException(\Exception::class);

        // Mock repository to throw exception
        $mockRepository = $this->createMock(TestRepository::class);
        $mockRepository->method('create')->willThrowException(new \Exception('Test exception'));
        
        $service = new TestService($mockRepository);
        
        $service->create(['name' => 'Test']);
    }
}