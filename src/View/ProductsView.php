<?php

declare(strict_types=1);

namespace Raketa\BackendTestTask\View;

use Raketa\BackendTestTask\Domain\Entity\Product;

readonly class ProductsView
{
    /**
     * @param Product[] $products
     */
    public function getProductsViewDataForCategory(array $products, string $categoryName): array
    {
        return array_map(
            fn (Product $product) => [
                'id' => $product->getId()->toString(),
                'name' => $product->getName(),
                'category' => $categoryName,
                'description' => $product->getDescription(),
                'thumbnail' => $product->getThumbnail(),
                'price' => $product->getPrice()->toString(),
            ],
            $products
        );
    }
}
