<?php
namespace RateLimiter;
use RateLimiter\Storage\StorageInterface;
use RateLimiter\Strategy\RateLimitStrategyInterface;

class RateLimiter
{
    private $storage;
    private $strategy;

    public static $globalRateLimitKey = 'global_rate_limit';
    public function __construct(StorageInterface $storage, RateLimitStrategyInterface $strategy)
    {
        $this->storage = $storage;
        $this->strategy = $strategy;
    }

    /**
     * 可自定义限流策略
     * @param string $key
     * @param int $limit
     * @param int $window
     * @return bool
     */
    public function allowRequest(string $key, int $limit , int $window): bool
    {
        return $this->strategy->allowRequest($key, $limit, $window);
    }
    /**
     * 全局限流策略
     */
    public function allowGlobalRequest(int $limit, int $window): bool
    {
        return $this->allowRequest(self::$globalRateLimitKey, $limit, $window);
    }
    /**
     * 根据用户id限流策略
     */
    public function allowUserRequest(int $userId, int $limit, int $window): bool
    {
        $userKey = "user:$userId";
        return $this->allowRequest($userKey, $limit, $window);
    }
    /**
     * 根据请求ip限流策略
     */
    public function allowIpRequest(string $ipAddress, int $limit, int $window): bool
    {
        $ipKey = "ip:$ipAddress";
        return $this->allowRequest($ipKey, $limit, $window);
    }

}