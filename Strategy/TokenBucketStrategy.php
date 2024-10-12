<?php
namespace RateLimiter\Strategy;
use RateLimiter\Storage\StorageInterface;
class TokenBucketStrategy implements RateLimitStrategyInterface
{
    private $storage;

    public function __construct(StorageInterface $storage)
    {
        $this->storage = $storage;
    }

    public function allowRequest(string $key, int $limit, int $window): bool
    {
        $currentTime = time();
        $bucket = $this->storage->get($key);

        if (!$bucket) {
            $bucket = [
                'tokens' => $limit,
                'last_time' => $currentTime
            ];
        } else {
            $bucket = json_decode($bucket, true);
        }

        $elapsed = $currentTime - $bucket['last_time'];
        $bucket['tokens'] += $elapsed * ($limit / $window);
        $bucket['tokens'] = min($limit, $bucket['tokens']);
        $bucket['last_time'] = $currentTime;

        if ($bucket['tokens'] < 1) {
            return false;
        }

        $bucket['tokens'] -= 1;
        $this->storage->set($key, json_encode($bucket), $window);

        return true;
    }
}