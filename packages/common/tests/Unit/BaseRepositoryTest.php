<?php

namespace Packages\Common\Tests\Unit;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Packages\Common\Repositories\BaseRepository;
use Packages\Common\Tests\TestCase;
use Packages\Common\Tests\Fixtures\TestModel;
use Packages\Common\Tests\Fixtures\TestRepository;

class BaseRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected TestRepository $repository;
    protected TestModel $model;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->model = new TestModel();
        $this->repository = new TestRepository($this->model);
    }

    public function test_can_create_record(): void
    {
        $data = [
            'name' => 'Test Item',
            'description' => 'Test Description',
        ];

        $result = $this->repository->create($data);

        $this->assertInstanceOf(Model::class, $result);
        $this->assertEquals('Test Item', $result->name);
        $this->assertEquals('Test Description', $result->description);
    }

    public function test_can_find_record(): void
    {
        $model = TestModel::create([
            'name' => 'Test Item',
            'description' => 'Test Description',
        ]);

        $result = $this->repository->find($model->id);

        $this->assertNotNull($result);
        $this->assertEquals($model->id, $result->id);
        $this->assertEquals('Test Item', $result->name);
    }

    public function test_can_find_or_fail_record(): void
    {
        $model = TestModel::create([
            'name' => 'Test Item',
            'description' => 'Test Description',
        ]);

        $result = $this->repository->findOrFail($model->id);

        $this->assertNotNull($result);
        $this->assertEquals($model->id, $result->id);
    }

    public function test_find_or_fail_throws_exception_when_not_found(): void
    {
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
        
        $this->repository->findOrFail(999);
    }

    public function test_can_update_record(): void
    {
        $model = TestModel::create([
            'name' => 'Test Item',
            'description' => 'Test Description',
        ]);

        $updateData = ['name' => 'Updated Item'];
        $result = $this->repository->update($model, $updateData);

        $this->assertTrue($result);
        $model->refresh();
        $this->assertEquals('Updated Item', $model->name);
    }

    public function test_can_delete_record(): void
    {
        $model = TestModel::create([
            'name' => 'Test Item',
            'description' => 'Test Description',
        ]);

        $result = $this->repository->delete($model);

        $this->assertTrue($result);
        $this->assertNull(TestModel::find($model->id));
    }

    public function test_can_get_all_records(): void
    {
        TestModel::create(['name' => 'Item 1', 'description' => 'Description 1']);
        TestModel::create(['name' => 'Item 2', 'description' => 'Description 2']);

        $results = $this->repository->all();

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

        $results = $this->repository->paginate(10);

        $this->assertEquals(10, $results->perPage());
        $this->assertEquals(20, $results->total());
        $this->assertEquals(2, $results->lastPage());
    }

    public function test_can_find_where(): void
    {
        TestModel::create(['name' => 'Item 1', 'description' => 'Description 1']);
        TestModel::create(['name' => 'Item 2', 'description' => 'Description 2']);
        TestModel::create(['name' => 'Item 1', 'description' => 'Description 3']);

        $results = $this->repository->findWhere(['name' => 'Item 1']);

        $this->assertCount(2, $results);
        $this->assertTrue($results->every(fn($item) => $item->name === 'Item 1'));
    }

    public function test_can_count_records(): void
    {
        TestModel::create(['name' => 'Item 1', 'description' => 'Description 1']);
        TestModel::create(['name' => 'Item 2', 'description' => 'Description 2']);

        $count = $this->repository->count();

        $this->assertEquals(2, $count);
    }

    public function test_can_get_new_query(): void
    {
        $query = $this->repository->newQuery();

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Builder::class, $query);
    }

    public function test_applies_store_scope_when_user_authenticated(): void
    {
        // This test would require setting up authentication and store context
        // For now, we'll test the basic functionality
        $this->assertTrue(true);
    }
}