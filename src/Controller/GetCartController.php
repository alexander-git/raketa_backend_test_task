<?php

declare(strict_types = 1);

namespace Raketa\BackendTestTask\Controller;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Raketa\BackendTestTask\Application\Service\Auth\AuthServiceInterface;
use Raketa\BackendTestTask\Application\Service\Auth\Exception\UnauthorizedException;
use Raketa\BackendTestTask\Domain\Exception\CartNotFoundException;
use Raketa\BackendTestTask\Domain\Service\CartService;
use Raketa\BackendTestTask\Infrastructure\Response\JsonResponseFactory;
use Raketa\BackendTestTask\View\CartView;

readonly class GetCartController
{
    public function __construct(
        private CartService $cartService,
        private AuthServiceInterface $authService,
        private CartView $cartView,
        private JsonResponseFactory $jsonResponseFactory,
    ) {
    }

    public function __invoke(RequestInterface $request): ResponseInterface
    {
        try {
            $userIdentifier = $this->authService->getUserIdentifier($request);
        } catch (UnauthorizedException $e) {
            return $this->jsonResponseFactory->createUnauthorizedResponse($e->getMessage());
        }

        try {
            $cartReadModel = $this->cartService->getCartReadModelByUserIdentifier($userIdentifier);
        } catch (CartNotFoundException) {
            return $this->jsonResponseFactory->createNotFoundResponse('Корзина не найдена');
        }

        return $this->jsonResponseFactory->createSuccessResponse(
            $this->cartView->getViewData($cartReadModel)
        );
    }
}
