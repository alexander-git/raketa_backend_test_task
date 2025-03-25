<?php

declare(strict_types=1);

namespace Raketa\BackendTestTask\Controller;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Raketa\BackendTestTask\Controller\Helper\RequestHelper;
use Raketa\BackendTestTask\Domain\Exception\CartNotFoundException;
use Raketa\BackendTestTask\Domain\Exception\ProductNotFoundException;
use Raketa\BackendTestTask\Domain\Service\CartService;
use Raketa\BackendTestTask\Infrastructure\Response\JsonResponseFactory;
use Raketa\BackendTestTask\Infrastructure\Service\Auth\AuthServiceInterface;
use Raketa\BackendTestTask\Infrastructure\Service\Auth\Exception\UnauthorizedException;

readonly class AddToCartController
{
    public function __construct(
        private CartService $cartService,
        private AuthServiceInterface $authService,
        private RequestHelper $requestHelper,
        private JsonResponseFactory $jsonResponseFactory
    ) {
    }

    public function __invoke(RequestInterface $request): ResponseInterface
    {
        try {
            $userIdentifier = $this->authService->getUserIdentifier($request);
        } catch (UnauthorizedException $e) {
            return $this->jsonResponseFactory->createUnauthorizedResponse($e->getMessage());
        }

        $requestData = $this->requestHelper->getDataFromJsonRequestBody($request);
        try {
            $this->cartService->addProductToUserCart(
                $userIdentifier,
                $requestData['productUuid'],
                $requestData['quantity']
            );
        } catch (ProductNotFoundException) {
            return $this->jsonResponseFactory->createUnprocessableEntityResponse('Продукт не найден');
        } catch (CartNotFoundException) {
            return $this->jsonResponseFactory->createNotFoundResponse('Корзина не найдена');
        }

        return $this->jsonResponseFactory->createEmptySuccessResponse();
    }
}
