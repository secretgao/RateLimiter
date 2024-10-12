<?php
namespace RateLimiter\Storage;
interface StorageInterface
{
    public function get(string $key);
    public function set(string $key, $value, int $ttl = null);
    public function delete(string $key);
}