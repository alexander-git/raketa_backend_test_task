<?php

declare(strict_types=1);

namespace Raketa\BackendTestTask\Domain\Entity;

use Raketa\BackendTestTask\Domain\Model\CartItemModel;

class Cart
{
    /**
     * @param CartItemModel[] $items
     */
    public function __construct(
        private readonly string $id,
        private array $items = [],
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return CartItemModel[]
     */
    public function getItems(): array
    {
        return $this->items;
    }

    public function addProduct(Product $product, int $quantity): void
    {
        foreach ($this->items as $key => $item) {
            if ($item->getProductId()->equals($product->getId())) {
                $this->items[$key] = new CartItemModel(
                    $product->getId(),
                    $product->getPrice(),
                    $item->getQuantity() + $quantity,
                );

                return;
            }
        }

        $this->items[] = new CartItemModel(
            $product->getId(),
            $product->getPrice(),
            $quantity
        );
    }
}
