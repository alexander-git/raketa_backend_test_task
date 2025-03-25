<?php

declare(strict_types=1);

namespace Raketa\BackendTestTask\Domain\Model;

use Raketa\BackendTestTask\Domain\ValueObject\Price;

readonly class CartReadModel
{
    /**
     * @param CartItemReadModel[] $items
     */
    public function __construct(
        private string $id,
        private array $items
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return CartItemReadModel[]
     */
    public function getItems(): array
    {
        return $this->items;
    }

    public function getTotalPrice(): Price
    {
        $totalPrice = new Price(0, 0);
        foreach ($this->items as $item) {
            $totalPrice = $totalPrice->add($item->getTotalPrice());
        }

        return $totalPrice;
    }
}
