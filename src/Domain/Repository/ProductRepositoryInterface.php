<?php

declare(strict_types=1);

namespace Raketa\BackendTestTask\Domain\Repository;

use Raketa\BackendTestTask\Domain\Entity\Product;
use Raketa\BackendTestTask\Domain\Exception\CategoryNotFoundException;
use Raketa\BackendTestTask\Domain\Exception\ProductNotFoundException;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

interface ProductRepositoryInterface
{
    /**
     * @throws ProductNotFoundException
     */
    public function getById(Uuid $id): Product;

    /**
     * @throws CategoryNotFoundException
     * @return Product[]
     */
    public function getActiveProductsByCategoryName(string $categoryName): array;

    /**
     * @param UuidInterface[] $ids
     * @return Product[]
     */
    public function getProductsByIdsIndexedById(array $ids): array;
}