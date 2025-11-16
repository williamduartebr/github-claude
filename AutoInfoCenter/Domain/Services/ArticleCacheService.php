<?php

namespace Src\AutoInfoCenter\Domain\Services;

use Illuminate\Support\Facades\Cache;

class ArticleCacheService
{
    private const CACHE_TTL = 43200; // 30 dias

    public function rememberArticle(string $slug, callable $callback): mixed
    {
        $key = "article:{$slug}";
        return Cache::remember($key, self::CACHE_TTL, $callback);
    }

    public function forgetArticle(string $slug): bool
    {
        return Cache::forget("article:{$slug}");
    }
}