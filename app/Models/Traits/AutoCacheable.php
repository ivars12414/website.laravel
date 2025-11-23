<?php

namespace App\Models\Traits;

use Illuminate\Support\Facades\Cache;

trait AutoCacheable
{
    /**
     * Включить кеш для модели?
     * если false — трейт ничего не делает.
     */
    protected static bool $cacheEnabled = true;

    /**
     * TTL в секундах.
     */
    protected static int $cacheTtl = 600;

    /**
     * Префикс для тегов/ключей модели.
     */
    protected static function cacheTag(): string
    {
        return static::class; // можно переопределять
    }

    /**
     * Собирает ключ кеша.
     */
    protected static function cacheKey(string $suffix, array $params = []): string
    {
        $base = static::cacheTag() . ':' . $suffix;
        if (!$params) return $base;

        return $base . ':' . md5(serialize($params));
    }

    /**
     * Универсальный remember для модели.
     */
    protected static function remember(string $suffix, array $params, \Closure $cb)
    {
        if (!static::$cacheEnabled) {
            return $cb();
        }

        $key = static::cacheKey($suffix, $params);

        // теги только если драйвер поддерживает
        $store = Cache::getStore();
        $supportsTags = method_exists($store, 'tags');

        if ($supportsTags) {
            return Cache::tags([static::cacheTag()])
                ->remember($key, static::$cacheTtl, $cb);
        }

        return Cache::remember($key, static::$cacheTtl, $cb);
    }

    /**
     * Сброс кеша модели целиком.
     */
    public static function flushModelCache(): void
    {
        if (!static::$cacheEnabled) return;

        $store = Cache::getStore();
        $supportsTags = method_exists($store, 'tags');

        if ($supportsTags) {
            Cache::tags([static::cacheTag()])->flush();
        }
        // если тегов нет — можно чистить точечно через forget в моделях
        // либо забить, если тегов нет в проде не используется
    }

    /**
     * Автосброс кеша при изменениях модели.
     */
    protected static function bootAutoCacheable(): void
    {
        static::saved(fn() => static::flushModelCache());
        static::deleted(fn() => static::flushModelCache());
//        static::restored(fn() => static::flushModelCache());
    }
}
