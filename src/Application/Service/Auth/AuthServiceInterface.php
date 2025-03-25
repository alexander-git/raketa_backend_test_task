<?php

declare(strict_types=1);

namespace Raketa\BackendTestTask\Application\Service\Auth;

use Psr\Http\Message\RequestInterface;
use Raketa\BackendTestTask\Application\Service\Auth\Exception\UnauthorizedException;

interface AuthServiceInterface
{
    /**
     * @throws UnauthorizedException
     */
    public function getUserIdentifier(RequestInterface $request): string;
}