<?php
namespace RateLimiter\Strategy;
use RateLimiter\Storage\StorageInterface;
class LeakyBucketStrategy implements RateLimitStrategyInterface
{
    private $storage;
    public function __construct(StorageInterface $storage) {
        $this->storage = $storage;
    }


    public function allowRequest(string $key, int $limit, int $window): bool
    {
        $currentTime = microtime(true);
        $bucket = $this->storage->get($key);

        if (!$bucket) {
            $bucket = ['water' => 0, 'lastTime' => $currentTime];
        }

        $elapsedTime = $currentTime - $bucket['lastTime'];
        $leakedWater = $elapsedTime * $limit;
        $bucket['water'] = max(0, $bucket['water'] - $leakedWater);
        $bucket['lastTime'] = $currentTime;

        if ($bucket['water'] < $window) {
            $bucket['water']++;
            $this->storage->set($key, $bucket);
            return true;
        } else {
            $this->storage->set($key, $bucket);
            return false;
        }
    }
}