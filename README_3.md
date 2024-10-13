
## 从0-1的车票项目
##### 项目背景
* 主要是为了开发商引流，提供免费打车看房服务
###### (项目简易流程图)[https://www.yuque.com/hongliyuyulvliyuyulvxoxo/hbwglt/isqpae2uzc3g669n?singleDoc]
##### 项目团队的组成
* 2个后端2个前端，担任负责人（包括开发，表结构设计，跟产品沟通需求，跨部门沟通，和滴滴开发调接口），算我后端开发3人，
##### 项目挑战
* 1.非技术方面，从0-1开发，从项目立项->排期->开发->跨部门协作沟通->联调(各个端：pc，h5，小程序，触屏，app)->测试->上线->后续优化   
* 2.技术方面，用户在页面点击领车票 相当于电商系统的秒杀抢商品
  
    * a) 车票扣库存
    * b）车票重复领取
    * c）保证redis 的高可用 使用哨兵模式
(redis-sentinel 故障自动转移)[https://github.com/secretgao/redis/blob/master/redis-sentinel.md]    
* 使用lua 脚本 在nginx 配置中    
```lua
local redis = require "resty.redis"
local cjson = require "cjson"

local function get_redis_master(sentinel_host, sentinel_port, master_name)
    local red = redis:new()
    red:set_timeout(1000) -- 1 second

    local ok, err = red:connect(sentinel_host, sentinel_port)
    if not ok then
        ngx.log(ngx.ERR, "链接 Redis Sentinel 失败: ", err)
        return nil, err
    end

    local res, err = red:sentinel("get-master-addr-by-name", master_name)
    if not res then
        ngx.log(ngx.ERR, "获取 master 节点失败: ", err)
        return nil, err
    end

    local master_ip = res[1]
    local master_port = res[2]
    return master_ip, master_port
end

local function connect_redis(host, port)
    local red = redis:new()
    red:set_timeout(1000) -- 1 second

    local ok, err = red:connect(host, port)
    if not ok then
        ngx.log(ngx.ERR, "链接 Redis 失败: ", err)
        return nil, err
    end

    return red
end

local function validate_param(param, param_name)
    if not param then
        ngx.say(cjson.encode({status = "fail", message = "参数错误：" .. param_name .. " 必传"}))
        return false
    end
    return true
end

-- 配置 Redis 哨兵信息
local sentinel_host = "your_sentinel_host"
local sentinel_port = 26379
local master_name = "mymaster"

-- 获取 Redis 主节点信息
local master_ip, master_port = get_redis_master(sentinel_host, sentinel_port, master_name)
if not master_ip or not master_port then
    ngx.say(cjson.encode({status = "fail", message = "获取 master 地址失败"}))
    return
end

-- 连接 Redis 主节点
local red, err = connect_redis(master_ip, master_port)
if not red then
    ngx.say(cjson.encode({status = "fail", message = "链接 redis 失败: " .. err}))
    return
end

-- 读取请求体
ngx.req.read_body()
local args, err = ngx.req.get_post_args()

if not args then
    ngx.say(cjson.encode({status = "fail", message = "参数错误: " .. err}))
    return
end

if not validate_param(args.phone, "手机号") or
   not validate_param(args.loupan_id, "楼盘") or
   not validate_param(args.activit_id, "活动id") then
    return
end

local phone = args.phone
local loupan_id = args.loupan_id
local activit_id = args.activit_id

-- 尝试设置缓存锁，如果已经存在则返回失败
local lock_key = "lock:" .. phone
local lock_ttl = 60 -- 设置锁的过期时间为60秒

local res, err = red:setnx(lock_key, true)
if not res then
    ngx.say(cjson.encode({status = "fail", message = "设置锁失败: " .. err}))
    return
end
-- 检测到并发请求
if res == 0 then
    ngx.say(cjson.encode({status = "fail", message = "活动太火爆请稍后再试"}))
    return
end

-- 设置锁的过期时间
local ok, err = red:expire(lock_key, lock_ttl)
if not ok then
    ngx.log(ngx.ERR, "设置锁过期时间失败: ", err)
    red:del(lock_key)
    ngx.say(cjson.encode({status = "fail", message = "设置锁过期时间失败"}))
    return
end

-- 车票活动库存缓存
local ticket_activity_cache = "ticket_total:" .. activit_id

-- 获取车票库存
local stock, err = red:get(ticket_activity_cache)
if not stock then
    ngx.log(ngx.ERR, "获取库存失败: ", err)
    red:del(lock_key)
    ngx.exit(500)
end

if tonumber(stock) <= 0 then
    ngx.say(cjson.encode({status = "fail", message = "该楼盘的车票不足，请联系客服"}))
    red:del(lock_key)
    return
end

-- 减少库存
local res, err = red:decr(ticket_activity_cache)
if not res then
    ngx.log(ngx.ERR, "减少库存失败: ", err)
    red:del(lock_key)
    ngx.exit(500)
end

-- push 消息队列
local ok, err = red:rpush("ticket_queue", cjson.encode({
    phone = phone,
    loupan_id = loupan_id,
    activit_id = activit_id
}))
if not ok then
    ngx.log(ngx.ERR, "推送到队列失败: ", err)
    red:del(lock_key)
    ngx.exit(500)
end

-- 处理完成，删除缓存锁
red:del(lock_key)

-- 释放 Redis 连接
red:set_keepalive(10000, 100)

ngx.say(cjson.encode({status = "success", message = "领取成功，请等候客服回电"}))
```       
