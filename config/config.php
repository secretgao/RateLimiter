<?php
//存储系统 的链接配置
return [
    'redis' => [
        'host' => '127.0.0.1',
        'port' => 6379,
        'timeout' => 0.0,
        'auth' => null,
        'database' => 0,
    ],
    'memcache' => [
        'host' => '127.0.0.1',
        'port' => 11211,
    ],
    // 可以在这里添加其他配置
];