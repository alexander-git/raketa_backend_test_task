<?php

declare(strict_types=1);

namespace Raketa\BackendTestTask\Infrastructure\Repository;

use Raketa\BackendTestTask\Domain\Entity\Cart;
use Raketa\BackendTestTask\Domain\Repository\CartRepositoryInterface;
use Raketa\BackendTestTask\Infrastructure\Exception\RedisConnectorException;
use Raketa\BackendTestTask\Infrastructure\Redis\RedisConnector;

readonly class CartRepository implements CartRepositoryInterface
{
    private const CART_TTL = 24 * 60 * 60;

    public function __construct(private RedisConnector $connector)
    {
    }

    /**
     * @throws RedisConnectorException
     */
    public function getCartById(string $id): Cart
    {
        $cart = $this->connector->get($this->getCartKeyByCartId($id));
        if ($cart !== null) {
            $cart = unserialize($cart);
        } else {
            $cart = new Cart($id, []);
        }

        return $cart;
    }

    /**
     * @throws RedisConnectorException
     */
    public function getCartByUserIdentifier(string $userIdentifier): Cart
    {
        return $this->getCartById($this->getCartIdByUserIdentifier($userIdentifier));
    }

    /**
     * @throws RedisConnectorException
     */
    public function saveCart(Cart $cart): void
    {
        $this->connector->setWithExpireTime(
            $this->getCartKeyByCartId($cart->getId()),
            serialize($cart),
            self::CART_TTL
        );
    }

    private function getCartKeyByCartId(string $id): string
    {
        return 'cart:' . $id;
    }

    private function getCartIdByUserIdentifier(string $userIdentifier): string
    {
        // Для упрощения примера id корзины соответсвует id пользователя.
        // Но если захотелось бы сделать так чтобы у корзины был свой id, например в виде того же Ramsey\Uuid\Uuid, то
        // можно было бы в Redis хранить соответсвие между id пользователя и id корзины. При сохранени корзины
        // также и сохранялось бы и это соответсвие. И при поиске корзины по id пользователя сначала бы искали
        // id корзины по id пользователя, а потом извлекали бы саму корзину.
        return $userIdentifier;
    }
}
