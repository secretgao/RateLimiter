<?php
namespace RateLimiter\Storage;
//定义存储接口 实现对应的 获取 存储 删除  继承本接口的类必须实现这些方法
interface StorageInterface
{
    public function get(string $key);
    public function set(string $key, $value, int $ttl = null);
    public function delete(string $key);
}