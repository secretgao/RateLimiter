<?php
namespace RateLimiter\Storage;
use Memcache;

class MemcacheStorage implements StorageInterface
{
    private $memcache;

    public function __construct(Memcache $Memcache)
    {
        $this->memcache = $Memcache;
    }

    public function get(string $key)
    {

    }

    public function set(string $key, $value, int $ttl = null)
    {

    }

    public function delete(string $key)
    {

    }
}