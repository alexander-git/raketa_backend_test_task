<?php

declare(strict_types=1);

namespace Raketa\BackendTestTask\Application\Service\Auth;

use Psr\Http\Message\RequestInterface;

class SessionIdAuthService implements AuthServiceInterface
{
    public function getUserIdentifier(RequestInterface $request): string
    {
        session_start();
        return session_id();
    }
}
