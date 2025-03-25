<?php

declare(strict_types = 1);

namespace Raketa\BackendTestTask\View;

use Raketa\BackendTestTask\Domain\Model\CartItemReadModel;
use Raketa\BackendTestTask\Domain\Model\CartReadModel;

readonly class CartView
{
    public function getViewData(CartReadModel $cartReadModel): array
    {
        $data['items'] = array_map(
            fn (CartItemReadModel $item) => [
                'productId' => $item->getProductId()->toString(),
                'price' => $item->getPrice()->toString(),
                'quantity' => $item->getQuantity(),
                'totalPrice' => $item->getTotalPrice()->toString(),
                'product' => [
                    'name' => $item->getProduct()->getName(),
                    'thumbnail' => $item->getProduct()->getThumbnail(),
                ],
            ],
            $cartReadModel->getItems()
        );

        $data['totalPrice'] = $cartReadModel->getTotalPrice()->toString();
        return $data;
    }
}
