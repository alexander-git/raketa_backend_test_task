<?php

declare(strict_types = 1);

namespace Raketa\BackendTestTask\Domain\Model;

use Raketa\BackendTestTask\Domain\Entity\Product;
use Raketa\BackendTestTask\Domain\ValueObject\Price;
use Ramsey\Uuid\UuidInterface;

readonly class CartItemReadModel
{
    public function __construct(
        private Product $product,
        private int $quantity,
    ) {
    }

    public function getProductId(): UuidInterface
    {
        return $this->product->getId();
    }

    public function getPrice(): Price
    {
        return $this->product->getPrice();
    }


    public function getProduct(): Product
    {
        return $this->product;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function getTotalPrice(): Price
    {
        return $this->getPrice()->multiple($this->quantity);
    }
}
