<?php

declare(strict_types=1);

namespace Raketa\BackendTestTask\Controller\Helper;

use InvalidArgumentException;
use Psr\Http\Message\RequestInterface;

class RequestHelper
{
    public function getDataFromJsonRequestBody(RequestInterface $request): array
    {
        return json_decode($request->getBody()->getContents(), true);
    }

    // Извлекает часть из uri которая после /$prefix/ и до следующего слеша или конца строки.
    public function getPartAfterPrefixFromUri(RequestInterface $request, string $prefix): ?string
    {
        if ($prefix === '') {
            throw new InvalidArgumentException();
        }

        $uriPath = $request->getUri()->getPath();
        $matches = [];
        if (preg_match("/\/$prefix\/([^\/]+)/", $uriPath, $matches)) {
            return $matches[1];
        }

        return null;
    }
}
