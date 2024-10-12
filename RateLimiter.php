<?php
namespace RateLimiter;
use RateLimiter\Storage\StorageInterface;
use RateLimiter\Strategy\RateLimitStrategyInterface;

class RateLimiter
{
    private $storage;
    private $strategy;

    public function __construct(StorageInterface $storage, RateLimitStrategyInterface $strategy)
    {
        $this->storage = $storage;
        $this->strategy = $strategy;
    }

    private function allowRequest(string $key, int $limit, int $window): bool
    {
        return $this->strategy->allowRequest($key, $limit, $window);
    }
    public function allowGlobalRequest(int $limit, int $window): bool
    {
        $globalKey = 'global_rate_limit';
        return $this->allowRequest($globalKey, $limit, $window);
    }

    public function allowUserRequest(int $userId, int $limit, int $window): bool
    {
        $userKey = "user:$userId";
        return $this->allowRequest($userKey, $limit, $window);
    }

    public function allowIpRequest(string $ipAddress, int $limit, int $window): bool
    {
        $ipKey = "ip:$ipAddress";
        return $this->allowRequest($ipKey, $limit, $window);
    }

}