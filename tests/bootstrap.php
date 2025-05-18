<?php

if (!function_exists('wp_cache_get')) {
    function wp_cache_get($key, $group = '', $force = false, &$found = null)
    {
        $found = false;
        return null;
    }
}

if (!function_exists('wp_cache_set')) {
    function wp_cache_set($key, $value, $group = '', $expire = 0)
    {
        return true;
    }
}

if (!function_exists('wp_cache_delete')) {
    function wp_cache_delete($key, $group = '')
    {
        return true;
    }
}

require_once __DIR__ . '/../vendor/autoload.php';
