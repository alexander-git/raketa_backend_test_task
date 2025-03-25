<?php

declare(strict_types=1);

namespace Raketa\BackendTestTask\Infrastructure\Redis;

use Redis;
use RedisException;
use Raketa\BackendTestTask\Infrastructure\Exception\RedisConnectorException;

readonly class RedisConnector
{
    public function __construct(private Redis $redis)
    {
    }

    /**
     * @throws RedisConnectorException
     */
    public function get(string $key): ?string
    {
        try {
            $value = $this->redis->get($key);
            return $value === false ? null : $value;
        } catch (RedisException $e) {
            throw new RedisConnectorException(message: "Ошибка при выполнении redis get с ключом $key", previous: $e);
        }
    }

    /**
     * @throws RedisConnectorException
     */
    public function setWithExpireTime(string $key, string $value, int $expireTime): void
    {
        try {
            $result = $this->redis->setex($key, $expireTime, $value);
            if ($result === false) {
                throw new RedisConnectorException("Не получилось выполнить redis setex с ключом $key");
            }
        } catch (RedisException $e) {
            throw new RedisConnectorException(message: "Ошибка при выполнении redis setex с ключом $key", previous: $e);
        }
    }
}
