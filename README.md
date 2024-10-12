# RateLimiter
* PHP语言实现一个灵活可扩展的API请求限流器 [RateLimiter]
    * 支持对不同请求对象进行限流控制，可以是全局限流（所有用户共享限额）或针对特定对象（例如，根据用户ID进行限流）
    * 至少实现两种常见的限流策略（如漏桶算法、令牌桶算法），并且能够方便地扩展更多限流策略
    * 默认使用Redis作为限流数据的存储系统，同时支持轻松切换或扩展为其他存储系统（如数据库、内存等）
#### 目录结构图介绍
```
├── RateLimiter
│   ├── RateLimiter.php
│   ├── RateLimiterFactory.php
│   ├── Storage                             //存储类型目录           
│   │   ├── MemcacheStorage.php             //Memcache 存储
│   │   ├── RedisStorage.php                //Redis存储
│   │   └── StorageInterface.php            //存储类型接口约束
│   ├── Strategy                            //策略目录
│   │   ├── LeakyBucketStrategy.php         //漏桶策略
│   │   ├── RateLimitStrategyInterface.php  //策略接口约束
│   │   └── TokenBucketStrategy.php         //令牌桶策略
│   ├── config                                 //配置文件目录 
│   │   └── config.php          //配置存储类型的链接信息（redis配置，memcache 配置）
│   └── index.php                          //调用demo 
```
#### api扩展
* 新增存储类型 
    * 在Storage目录下创建新的存储类型
    * 可以在这个目录新增存储类型文件，按照StorageInterface.php 实现接口定义即可
    * RateLimiterFactory.php 工厂里新增对应的存储类型
    ```
        //默认定义存储类型
        public static $storageTypeRedis = 'redis';
        public static $storageTypeMemcache = 'memcache';
        //新增的存储类型
        public static $storageTypeXx = 'Xx';

        createStorage 方法 里的 switch 新增对应的 case 

    ```
*  新增策略
    * 在Strategy目录下创建新的策略
    * 可以在这个目录新增策略文件，按照 RateLimitStrategyInterface 实现接口定义即可
    * RateLimiterFactory.php 工厂里新增对应的策略

    ```
      //定义限流策略类型
      public static $strategyTypeLeaky = 'leaky_bucket';
      public static $strategyTypeToken = 'token_bucket';
      //新增的限流策略
      public static $strategyTypeXx = 'Xx';
      createStrategy 方法 里的 switch 新增对应的 case 

    ```

    