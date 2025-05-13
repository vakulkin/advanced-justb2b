<?php

namespace JustB2b\Traits;

defined('ABSPATH') || exit;

trait RuntimeCacheTrait
{
    protected function getFromRuntimeCache(string $cacheKey, callable $callback, string $group = 'justb2b_fields'): mixed
    {
        $value = wp_cache_get($cacheKey, $group);
        if ($value === false) {
            $value = $callback();
            wp_cache_set($cacheKey, $value, $group);
        }
        return $value;
    }

    protected function clearRuntimeCache(string $cacheKey, string $group = 'justb2b_fields'): void
    {
        wp_cache_delete($cacheKey, $group);
    }
}
