<?php

declare(strict_types=1);

namespace Raketa\BackendTestTask\Domain\ValueObject;

use InvalidArgumentException;

readonly class Price
{
    private int $rubles;
    private int $kopecks;

    public function __construct(int $rubles, int $kopecks = 0) {
        if ($rubles < 0) {
            throw new InvalidArgumentException('Рубли должны быть неотрицательными');
        }

        if ($kopecks < 0) {
            throw new InvalidArgumentException('Копейки должны быть неотрицательными');
        }

        if ($kopecks > 99) {
            throw new InvalidArgumentException('Число копеек не должнл быть больше 99');
        }

        $this->rubles = $rubles;
        $this->kopecks = $kopecks;
    }

    public function getRubles(): int
    {
        return $this->rubles;
    }

    public function getKopecks(): int
    {
        return $this->kopecks;
    }

    public function equals(self $otherPrice): bool
    {
        return
            $this->rubles === $otherPrice->rubles &&
            $this->kopecks === $otherPrice->kopecks;
    }

    public function multiple(int $factor): self
    {
        if ($factor < 0) {
            throw new InvalidArgumentException('Множитель должн быть неотрицательными');
        }

        return self::fromKopecks($this->getAmountKopecks() * $factor);
    }

    public function add(Price $otherPrice): self
    {
        return self::fromKopecks($this->getAmountKopecks() + $otherPrice->getAmountKopecks());
    }

    public function toString(): string
    {
        return (string)$this;
    }

    public function __toString(): string
    {
        return sprintf("%d.%02d", $this->rubles, $this->kopecks);
    }

    public static function fromString(string $productPriceStr): self
    {
        if (preg_match('/^[1-9]\d*\.\d{2}$/', $productPriceStr) === 0) {
            throw new InvalidArgumentException('Невернй формат строки с ценой');
        }

        list($rublesStr, $kopecksStr) = explode('.', $productPriceStr);
        if (str_starts_with($kopecksStr, '0')) {
            $kopecksStr = substr($kopecksStr, 1);
        }

        return new self(
            (int) $rublesStr,
            (int) $kopecksStr
        );
    }

    private function getAmountKopecks(): int
    {
        return ($this->rubles * 100) + $this->kopecks;
    }

    private static function fromKopecks(int $amountKopecks): self
    {
        if ($amountKopecks < 0) {
            throw new InvalidArgumentException('Цена в копейках должна быть неотрицательной');
        }

        return new self(
            intdiv($amountKopecks, 100),
            $amountKopecks % 100
        );
    }
}
