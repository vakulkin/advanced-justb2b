<?php

namespace JustB2b\Utils;

if (!defined('ABSPATH')) {
    exit;
}

class Prefixer
{
    protected static $prefix = 'justb2b';

    public static function getPrefixed($value)
    {
        $prefix = self::$prefix;
        return "{$prefix}_{$value}";
    }

    public static function getPrefixedMeta($value)
    {
        $prefixed = self::getPrefixed($value);
        return "_{$prefixed}";
    }

    public static function getTextdomain()
    {
        return self::$prefix;
    }

}