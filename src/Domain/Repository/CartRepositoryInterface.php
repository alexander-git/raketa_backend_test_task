<?php

declare(strict_types=1);

namespace Raketa\BackendTestTask\Domain\Repository;

use Raketa\BackendTestTask\Domain\Entity\Cart;
use Raketa\BackendTestTask\Domain\Exception\CartNotFoundException;
use Raketa\BackendTestTask\Domain\Exception\UserNotFoundException;

interface CartRepositoryInterface
{
    /**
     * @throws CartNotFoundException
     */
    public function getCartById(string $id): Cart;

    /**
     * @throws CartNotFoundException
     * @throws UserNotFoundException
     */
    public function getCartByUserIdentifier(string $userIdentifier): Cart;

    public function saveCart(Cart $cart): void;
}