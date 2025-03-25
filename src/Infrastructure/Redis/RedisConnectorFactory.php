<?php

declare(strict_types=1);

namespace Raketa\BackendTestTask\Infrastructure\Redis;

use Psr\Log\LoggerInterface;
use Raketa\BackendTestTask\Infrastructure\Exception\RedisConnectorException;
use Redis;
use RedisException;

readonly class RedisConnectorFactory
{
    public function __construct(
        private LoggerInterface $logger
    ) {
    }

    /**
     * @throws RedisConnectorException
     * @throws RedisException
     */
    public function create(string $host, int $port = 6379, string $password = '', int $dbIndex = 0): RedisConnector
    {
        $redis = new Redis();
        try {
            $isConnected = $redis->connect($host, $port);

            if ($isConnected && $redis->ping('pong') !== 'pong') {
                $isConnected = false;
            }

            if (!$isConnected) {
                throw new RedisConnectorException('Не удалось подключиться к Redis');
            }

            if (!$redis->auth($password)) {
                throw new RedisConnectorException('Не удалось аунтефицироваться в Redis');
            }

            if (!$redis->select($dbIndex)) {
                throw new RedisConnectorException('Не удалось выбрать индекс Redis');
            }

            return new RedisConnector($redis);
        } catch (RedisConnectorException|RedisException $e) {
            $message = $e->getMessage();
            $message = $message === '' ? 'Не удалось создать коннектор к Redis' : $message;
            $this->logger->error($message);
            throw $e;
        }
    }
}
