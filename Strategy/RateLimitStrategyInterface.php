<?php

namespace RateLimiter\Strategy;

interface RateLimitStrategyInterface
{
    public function allowRequest(string $key, int $limit, int $window): bool;
}