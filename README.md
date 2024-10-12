# RateLimiter
#### 目录结构图
···
├── RateLimiter
│   ├── RateLimiter.php
│   ├── RateLimiterFactory.php
│   ├── Storage
│   │   ├── MemcacheStorage.php
│   │   ├── RedisStorage.php
│   │   └── StorageInterface.php
│   ├── Strategy
│   │   ├── LeakyBucketStrategy.php
│   │   ├── RateLimitStrategyInterface.php
│   │   └── TokenBucketStrategy.php
│   ├── config
│   │   └── config.php
│   └── index.php
···
