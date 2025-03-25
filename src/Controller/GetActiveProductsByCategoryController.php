<?php

declare(strict_types = 1);

namespace Raketa\BackendTestTask\Controller;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Raketa\BackendTestTask\Controller\Helper\RequestHelper;
use Raketa\BackendTestTask\Domain\Exception\CategoryNotFoundException;
use Raketa\BackendTestTask\Domain\Service\ProductService;
use Raketa\BackendTestTask\Infrastructure\Response\JsonResponseFactory;
use Raketa\BackendTestTask\View\ProductsView;

readonly class GetActiveProductsByCategoryController
{
    public function __construct(
        private RequestHelper $requestHelper,
        private ProductService $productService,
        private ProductsView $productsView,
        private JsonResponseFactory $jsonResponseFactory,
    ) {
    }

    public function invoke(RequestInterface $request): ResponseInterface
    {
        $categoryName = $this->requestHelper->getPartAfterPrefixFromUri($request, 'category');
        if ($categoryName === null) {
            return $this->jsonResponseFactory->createBadRequestResponse();
        }

        try {
            $products = $this->productService->getActiveProductsByCategoryName($categoryName);
        } catch (CategoryNotFoundException) {
            return $this->jsonResponseFactory->createNotFoundResponse("Категория не найдена");
        }

        return $this->jsonResponseFactory->createSuccessResponse(
            $this->productsView->getProductsViewDataForCategory($products, $categoryName)
        );
    }
}
