<?php

namespace JustB2b\Traits;

defined('ABSPATH') || exit;

trait RuntimeCacheTrait
{
    /**
     * @feature-section performance
     * @title[ru] Производительность и кэширование
     * @desc[ru] JustB2B включает интеллектуальное кэширование значений во время выполнения, что снижает нагрузку на сервер и ускоряет генерацию цен и условий.
     * @order 60
     */

    /**
     * @feature performance runtime_cache
     * @title[ru] Кэширование на лету (runtime cache)
     * @desc[ru] Ускоряет расчёты и снижает нагрузку на сервер за счёт сохранения промежуточных результатов — без записи в базу данных.
     * @order 61
     */

    /**
     * Enable or disable debug logging for cache operations.
     */
    public static bool $debugRuntimeCache = false;

    /**
     * Retrieve value from runtime cache or generate and cache it if not found.
     */
    protected static function getFromRuntimeCache(
        callable $callback,
        array $context = [],
        string $group = 'justb2b_plugin'
    ): mixed {
        $key = static::generateCacheKey($context);
        $value = wp_cache_get($key, $group, false, $found);

        static::logCacheEvent('GET', $key, $group, $found ? 'HIT' : 'MISS');

        if (!$found) {
            $value = $callback();
            wp_cache_set($key, $value, $group);
            static::logCacheEvent('SET', $key, $group);
        }

        return $value;
    }

    /**
     * Clear a specific runtime cache entry based on context.
     */
    protected static function clearRuntimeCache(array $context = [], string $group = 'justb2b_plugin'): void
    {
        $key = static::generateCacheKey($context);
        wp_cache_delete($key, $group);
        static::logCacheEvent('DELETE', $key, $group);
    }

    /**
     * Generate a unique cache key based on caller and context.
     */
    protected static function generateCacheKey(array $context = []): string
    {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3);
        $caller = $trace[2] ?? $trace[1] ?? [];

        $class = $caller['class'] ?? 'unknown_class';
        $method = $caller['function'] ?? 'unknown_method';

        ksort($context);
        $contextHash = !empty($context) ? ':' . md5(json_encode($context)) : '';

        return "{$class}::{$method}{$contextHash}";
    }

    /**
     * Expose the generated cache key for debugging.
     */
    protected static function debugRuntimeCacheKey(array $context = []): string
    {
        return static::generateCacheKey($context);
    }

    /**
     * Log a cache operation if debug is enabled.
     */
    protected static function logCacheEvent(string $action, string $key, string $group, string $result = ''): void
    {
        if (!static::$debugRuntimeCache) {
            return;
        }

        $message = "[CACHE {$action}] {$key} | Group: {$group}";
        if ($result !== '') {
            $message .= " | Result: {$result}";
        }

        error_log($message);
    }
}
