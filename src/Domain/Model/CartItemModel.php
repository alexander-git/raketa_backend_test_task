<?php

declare(strict_types = 1);

namespace Raketa\BackendTestTask\Domain\Model;

use Raketa\BackendTestTask\Domain\ValueObject\Price;
use Ramsey\Uuid\UuidInterface;

readonly class CartItemModel
{
    public function __construct(
        private UuidInterface $productId,
        private Price $price,
        private int $quantity,
    ) {
    }

    public function getProductId(): UuidInterface
    {
        return $this->productId;
    }

    public function getProductPrice(): Price
    {
        return $this->price;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }
}
