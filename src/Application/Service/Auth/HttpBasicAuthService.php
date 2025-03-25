<?php

declare(strict_types=1);

namespace Raketa\BackendTestTask\Application\Service\Auth;

use Psr\Http\Message\RequestInterface;
use Raketa\BackendTestTask\Application\Service\Auth\Exception\UnauthorizedException;

class HttpBasicAuthService implements AuthServiceInterface
{
    public function getUserIdentifier(RequestInterface $request): string
    {
        $authHeader = $request->getHeaderLine('Authorization');
        if (!str_starts_with($authHeader, 'Basic ')) {
            throw new UnauthorizedException('Неверный способ авторизации');
        }

        $encodedCredentials = substr($authHeader, 6);
        $decodedCredentials = base64_decode($encodedCredentials, true);
        if ($decodedCredentials === false) {
            throw new UnauthorizedException('Должно быть закодировано в  Base64');
        }

        list($username, $password) = explode(':', $decodedCredentials, 2);
        foreach ($this->getUsersList() as $user) {
            if ($user['username'] === $username && $user['password'] === $password) {
                return $user['id'];
            }
        }

        throw new UnauthorizedException();
    }

    private function getUsersList(): array
    {
        return [
            [
                'id' => '1',
                'username' => 'user1',
                'password' => 'password1',
            ],
            [
                'id' => '2',
                'username' => 'user2',
                'password' => 'password2',
            ],
            [
                'id' => '3',
                'username' => 'user3',
                'password' => 'password3',
            ],
        ];
    }
}