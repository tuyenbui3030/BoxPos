<?php

namespace Packages\Product\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Packages\Product\Models\Product;

class ProductRepository implements ProductRepositoryInterface
{
    public function getAllForStore(int $storeId, array $criteria = []): Collection
    {
        return Product::forStore($storeId)
            ->applyCriteria($criteria)
            ->withRelations()
            ->orderByName()
            ->get();
    }

    public function getPaginatedForStore(int $storeId, array $criteria = [], int $perPage = 15): LengthAwarePaginator
    {
        return Product::forStore($storeId)
            ->applyCriteria($criteria)
            ->withRelations()
            ->orderByName()
            ->paginate($perPage);
    }

    public function findForStore(int $id, int $storeId): ?Product
    {
        return Product::forStore($storeId)
            ->withRelations()
            ->find($id);
    }

    public function findByCodeForStore(string $code, int $storeId): ?Product
    {
        return Product::forStore($storeId)
            ->where('code', $code)
            ->withRelations()
            ->first();
    }

    public function findByBarcodeForStore(string $barcode, int $storeId): ?Product
    {
        return Product::forStore($storeId)
            ->where('barcode', $barcode)
            ->withRelations()
            ->first();
    }

    public function create(array $data): Product
    {
        return Product::create($data);
    }

    public function update(Product $product, array $data): Product
    {
        $product->update($data);
        return $product->fresh();
    }

    public function delete(Product $product): bool
    {
        return $product->delete();
    }

    public function getByCategory(int $categoryId, int $storeId): Collection
    {
        return Product::forStore($storeId)
            ->inCategory($categoryId)
            ->active()
            ->withRelations()
            ->orderByName()
            ->get();
    }

    public function getLowStockProducts(int $storeId): Collection
    {
        return Product::forStore($storeId)
            ->trackStock()
            ->lowStock()
            ->active()
            ->withRelations()
            ->orderByName()
            ->get();
    }

    public function search(string $query, int $storeId, int $limit = 10): Collection
    {
        return Product::forStore($storeId)
            ->search($query)
            ->active()
            ->withRelations()
            ->limit($limit)
            ->get();
    }

    public function getActiveForStore(int $storeId): Collection
    {
        return Product::forStore($storeId)
            ->active()
            ->withRelations()
            ->orderByName()
            ->get();
    }
}