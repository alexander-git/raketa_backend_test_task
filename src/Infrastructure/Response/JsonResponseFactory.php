<?php

declare(strict_types=1);

namespace Raketa\BackendTestTask\Infrastructure\Response;

use Nyholm\Psr7\Response;
use Psr\Http\Message\ResponseInterface;

class JsonResponseFactory
{
    public function create(array $data = [], int $status = 200, array $headers = []): ResponseInterface
    {
        $headers['Content-Type'] = 'application/json';

        return new Response(
            $status,
            $headers,
            json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
        );
    }

    public function createSuccessResponse(array $data = []): ResponseInterface
    {
        return $this->create($data);
    }

    public function createEmptySuccessResponse(): ResponseInterface
    {
        return $this->create();
    }

    public function createNotFoundResponse(string $message = ''): ResponseInterface
    {
        return $this->createSimpleMessageOrEmptyResponse($message, 404);
    }

    public function createUnauthorizedResponse(string $message = ''): ResponseInterface
    {
        return $this->createSimpleMessageOrEmptyResponse($message, 401);
    }

    public function createUnprocessableEntityResponse(string $message = ''): ResponseInterface
    {
        return $this->createSimpleMessageOrEmptyResponse($message, 422 );
    }

    public function createBadRequestResponse(string $message = ''): ResponseInterface
    {
        return $this->createSimpleMessageOrEmptyResponse($message,400);
    }

    private function createSimpleMessageOrEmptyResponse(string $message, int $status): ResponseInterface
    {
        $data = [];
        if ($message !== '') {
            $data['message'] = $message;
        }

        return $this->create($data, $status);
    }
}
