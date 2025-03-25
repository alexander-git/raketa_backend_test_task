<?php

declare(strict_types=1);

namespace Raketa\BackendTestTask\Domain\Service;

use Raketa\BackendTestTask\Domain\Entity\Cart;
use Raketa\BackendTestTask\Domain\Exception\CartNotFoundException;
use Raketa\BackendTestTask\Domain\Exception\ProductNotFoundException;
use Raketa\BackendTestTask\Domain\Exception\UserNotFoundException;
use Raketa\BackendTestTask\Domain\Model\CartItemModel;
use Raketa\BackendTestTask\Domain\Model\CartItemReadModel;
use Raketa\BackendTestTask\Domain\Model\CartReadModel;
use Raketa\BackendTestTask\Domain\Repository\CartRepositoryInterface;
use Raketa\BackendTestTask\Domain\Repository\ProductRepositoryInterface;
use Ramsey\Uuid\Uuid;
use RuntimeException;

readonly class CartService
{
    public function __construct(
        private CartRepositoryInterface $cartRepository,
        private ProductRepositoryInterface $productRepository
    ) {
    }

    /**
     * @throws CartNotFoundException
     */
    public function getCartById(string $id): Cart
    {
        return $this->cartRepository->getCartById($id);
    }

    /**
     * @throws CartNotFoundException
     * @throws UserNotFoundException
     */
    public function getCartByUserIdentifier(string $userIdentifier): Cart
    {
        return $this->cartRepository->getCartByUserIdentifier($userIdentifier);
    }

    /**
     * @throws CartNotFoundException
     * @throws ProductNotFoundException
     * @throws UserNotFoundException
     */
    public function addProductToUserCart(string $userIdentifier, Uuid $productId, int $quantity): void
    {
        $cart = $this->getCartByUserIdentifier($userIdentifier);
        $product = $this->productRepository->getById($productId);
        $cart->addProduct($product, $quantity);
        $this->cartRepository->saveCart($cart);
    }

    /**
     * @throws CartNotFoundException
     */
    public function getCartReadModelById(string $id): CartReadModel
    {
        return $this->getCartReadModelByCart($this->getCartById($id));
    }

    /**
     * @throws UserNotFoundException
     * @throws CartNotFoundException
     */
    public function getCartReadModelByUserIdentifier(string $userIdentifier): CartReadModel
    {
        return $this->getCartReadModelByCart($this->getCartByUserIdentifier($userIdentifier));
    }

    private function getCartReadModelByCart(Cart $cart): CartReadModel
    {
        $productIds = array_map(
            fn (CartItemModel $cartItem) => $cartItem->getProductId(),
            $cart->getItems()
        );

        $products = $this->productRepository->getProductsByIdsIndexedById($productIds);

        $cartItemReadModels = [];
        foreach ($cart->getItems() as $item) {
            $productIdStr = $item->getProductId()->toString();
            if (!array_key_exists($productIdStr, $products)) {
                throw new RuntimeException(); // Товара уже нет в базе данных.
            }

            $cartItemReadModels[] = new CartItemReadModel($products[$productIdStr], $item->getQuantity());
        }

        return new CartReadModel($cart->getId(), $cartItemReadModels);
    }
}
