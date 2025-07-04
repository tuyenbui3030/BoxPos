<?php

namespace Packages\Product\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Packages\Product\Models\Product;

interface ProductRepositoryInterface
{
    /**
     * Get all products for a store
     */
    public function getAllForStore(int $storeId, array $criteria = []): Collection;

    /**
     * Get paginated products for a store
     */
    public function getPaginatedForStore(int $storeId, array $criteria = [], int $perPage = 15): LengthAwarePaginator;

    /**
     * Find product by ID for a store
     */
    public function findForStore(int $id, int $storeId): ?Product;

    /**
     * Find product by code for a store
     */
    public function findByCodeForStore(string $code, int $storeId): ?Product;

    /**
     * Find product by barcode for a store
     */
    public function findByBarcodeForStore(string $barcode, int $storeId): ?Product;

    /**
     * Create a new product
     */
    public function create(array $data): Product;

    /**
     * Update a product
     */
    public function update(Product $product, array $data): Product;

    /**
     * Delete a product
     */
    public function delete(Product $product): bool;

    /**
     * Get products by category
     */
    public function getByCategory(int $categoryId, int $storeId): Collection;

    /**
     * Get low stock products
     */
    public function getLowStockProducts(int $storeId): Collection;

    /**
     * Search products
     */
    public function search(string $query, int $storeId, int $limit = 10): Collection;

    /**
     * Get active products for a store
     */
    public function getActiveForStore(int $storeId): Collection;
}