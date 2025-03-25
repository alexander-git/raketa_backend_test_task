<?php

declare(strict_types = 1);

namespace Raketa\BackendTestTask\Infrastructure\Repository;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Raketa\BackendTestTask\Domain\Entity\Product;
use Raketa\BackendTestTask\Domain\Exception\CategoryNotFoundException;
use Raketa\BackendTestTask\Domain\Exception\ProductNotFoundException;
use Raketa\BackendTestTask\Domain\Repository\ProductRepositoryInterface;
use Raketa\BackendTestTask\Domain\ValueObject\Price;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

readonly class ProductRepository implements ProductRepositoryInterface
{
    public function __construct(private Connection $connection)
    {
    }

    /**
     * @throws ProductNotFoundException
     * @throws Exception
     */
    public function getById(Uuid $id): Product
    {
        $row = $this->connection->fetchAssociative(
            "SELECT * FROM products WHERE id = :id",
            ['id' => $id->toString()]
        );

        if ($row === false) {
            throw new ProductNotFoundException();
        }

        return $this->makeProduct($row);
    }

    /**
     * @return Product[]
     * @throws CategoryNotFoundException
     * @throws Exception
     */
    public function getActiveProductsByCategoryName(string $categoryName): array
    {
        $categoryId = $this->connection->fetchOne(
            "SELECT id FROM categories WHERE name = :categoryName",
            ['categoryName' => $categoryName],
        );

        if ($categoryId === false) {
            throw new CategoryNotFoundException();
        }

        return array_map(
            fn (array $row): Product => $this->makeProduct($row),
            $this->connection->fetchAllAssociative(
                "SELECT * FROM products WHERE is_active = 1 AND category_id = :categoryId",
                ['categoryId' => $categoryId]
            )
        );
    }

    /**
     * @param UuidInterface[] $ids
     * @return Product[]
     * @throws Exception
     */
    public function getProductsByIdsIndexedById(array $ids): array
    {
        return array_reduce(
            $this->getProductsByIds($ids),
            function (array $indexedProducts, Product $product) {
                $indexedProducts[$product->getId()->toString()] = $product;
                return $indexedProducts;
            },
            []
        );
    }

    /**
     * @param UuidInterface[] $ids
     * @return Product[]
     * @throws Exception
     */
    private function getProductsByIds(array $ids): array
    {
        $productIds = array_map(fn (UuidInterface $uuid) => $uuid->toString(), $ids);

        return array_map(
            fn (array $row): Product => $this->makeProduct($row),
            $this->connection->fetchAllAssociative(
                "SELECT * FROM products WHERE id in (:ids)",
                ['ids' => $productIds],
            )
        );
    }

    private function makeProduct(array $row): Product
    {
        return new Product(
            Uuid::fromString($row['uuid']),
            $row['is_active'],
            Uuid::fromString($row['category']),
            $row['name'],
            $row['description'],
            $row['thumbnail'],
            Price::fromString($row['price']),
        );
    }
}
