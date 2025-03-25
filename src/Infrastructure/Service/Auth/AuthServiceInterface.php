<?php

declare(strict_types=1);

namespace Raketa\BackendTestTask\Infrastructure\Service\Auth;

use Psr\Http\Message\RequestInterface;
use Raketa\BackendTestTask\Infrastructure\Service\Auth\Exception\UnauthorizedException;

interface AuthServiceInterface
{
    /**
     * @throws UnauthorizedException
     */
    public function getUserIdentifier(RequestInterface $request): string;
}