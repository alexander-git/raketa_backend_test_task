<?php

declare(strict_types = 1);

namespace Raketa\BackendTestTask\Domain\Entity;

use Raketa\BackendTestTask\Domain\ValueObject\Price;
use Ramsey\Uuid\UuidInterface;

readonly class Product
{
    public function __construct(
        private UuidInterface $id,
        private bool $isActive,
        private UuidInterface $categoryId,
        private string $name,
        private ?string $description,
        private ?string $thumbnail,
        private Price $price
    ) {
    }

    public function getId(): UuidInterface
    {
        return $this->id;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function getCategoryId(): UuidInterface
    {
        return $this->categoryId;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getThumbnail(): ?string
    {
        return $this->thumbnail;
    }

    public function getPrice(): Price
    {
        return $this->price;
    }
}
