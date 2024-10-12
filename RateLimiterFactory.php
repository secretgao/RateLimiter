<?php
namespace RateLimiter;
use RateLimiter\Storage\MemcacheStorage;
use RateLimiter\Storage\RedisStorage;
use RateLimiter\Storage\StorageInterface;
use RateLimiter\Strategy\LeakyBucketStrategy;
use RateLimiter\Strategy\TokenBucketStrategy;
use RateLimiter\Strategy\RateLimitStrategyInterface;
use Redis;

class RateLimiterFactory
{
    private static $config;

    public static function initConfig(array $config)
    {
        self::$config = $config;
    }
    //定义存储类型
    public static $storageTypeRedis = 'redis';
    public static $storageTypeMemcache = 'memcache';

    //定义限流策略类型
    public static $strategyTypeLeaky = 'leaky_bucket';
    public static $strategyTypeToken = 'token_bucket';

    public static function create(string $storageType, string $strategyType): RateLimiter
    {
        //创建存储类型
        $storage = self::createStorage($storageType);
        //创建限流策略
        $strategy = self::createStrategy($strategyType, $storage);
        return new RateLimiter($storage, $strategy);
    }

    private static function createStorage(string $storageType): StorageInterface
    {
        switch ($storageType) {
            case self::$storageTypeRedis:
                $redisConfig = self::$config['redis'];
                $redis = new Redis();
                $redis->connect($redisConfig['host'], $redisConfig['port'], $redisConfig['timeout']);

                if (!empty($redisConfig['auth'])) {
                    $redis->auth($redisConfig['auth']);
                }

                $redis->select($redisConfig['database']);
                return new RedisStorage($redis);

            case self::$storageTypeMemcache:
                $memcacheConfig = self::$config['memcache'];
                $memcache = new \Memcache();
                $memcache->connect($memcacheConfig['host'], $memcacheConfig['port']);
                return new MemcacheStorage($memcache);
            // 可以在这里添加其他存储系统的初始化
            default:
                throw new \InvalidArgumentException("Unsupported storage type: $storageType");
        }
    }

    private static function createStrategy(string $strategyType, StorageInterface $storage): RateLimitStrategyInterface
    {
        switch ($strategyType) {
            case self::$strategyTypeLeaky:
                return new LeakyBucketStrategy($storage);
            case self::$strategyTypeToken:
                return new TokenBucketStrategy($storage);
            // 可以在这里添加其他策略的初始化
            default:
                throw new \InvalidArgumentException("Unsupported strategy type: $strategyType");
        }
    }
}