<?php
use RateLimiter\RateLimiterFactory;
// 加载配置文件
$config = require 'config/config.php';
// 初始化配置
RateLimiterFactory::initConfig($config);

//选择指定的存储类型和策略类型
$storageType = RateLimiterFactory::$storageTypeRedis;
$strategyType = RateLimiterFactory::$strategyTypeLeaky;

try {
    $rateLimiter = RateLimiterFactory::create($storageType, $strategyType);
    // 全局限流
    $globalLimit = 1000; // 全局限流 1000 次请求
    $globalWindow = 60; // 每 60 秒

    if ($rateLimiter->allowGlobalRequest($globalLimit, $globalWindow)) {
        echo "Global Request allowed\n";
    } else {
        echo "Global Request denied\n";
    }
    // 针对特定用户限流
    $userId = 123; // 例如，用户ID
    $userLimit = 10; // 用户限流 10 次请求
    $userWindow = 60; // 每 60 秒
    if ($rateLimiter->allowUserRequest($userId,$userLimit, $userWindow)) {
        echo "User Request allowed\n";
    } else {
        echo "User Request denied\n";
    }

    // 针对特定IP地址限流
    $ipAddress = '192.168.1.1'; // 例如，IP地址
    $ipLimit = 20; // IP地址限流 20 次请求
    $ipWindow = 60; // 每 60 秒
    if ($rateLimiter->allowIpRequest($ipAddress,$ipLimit, $ipWindow)) {
        echo "Ip Request allowed\n";
    } else {
        echo "Ip Request denied\n";
    }

} catch (\InvalidArgumentException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
