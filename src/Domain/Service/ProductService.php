<?php

declare(strict_types=1);

namespace Raketa\BackendTestTask\Domain\Service;

use Raketa\BackendTestTask\Domain\Entity\Cart;
use Raketa\BackendTestTask\Domain\Entity\Product;
use Raketa\BackendTestTask\Domain\Exception\CategoryNotFoundException;
use Raketa\BackendTestTask\Domain\Exception\ProductNotFoundException;
use Raketa\BackendTestTask\Domain\Model\CartItemModel;
use Raketa\BackendTestTask\Domain\Repository\ProductRepositoryInterface;
use Ramsey\Uuid\Uuid;

readonly class ProductService
{
    public function __construct(private ProductRepositoryInterface $productRepository)
    {
    }

    /**
     * @throws ProductNotFoundException
     */
    public function getById(Uuid $uuid): Product
    {
        return $this->productRepository->getById($uuid);
    }

    /**
     * @throws CategoryNotFoundException
     */
    public function getActiveProductsByCategoryName(string $categoryName): array
    {
        return $this->productRepository->getActiveProductsByCategoryName($categoryName);
    }
}