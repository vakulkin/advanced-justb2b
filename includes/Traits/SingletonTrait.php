<?php

namespace JustB2b\Traits;

defined('ABSPATH') || exit;

trait SingletonTrait
{
    private static array $instances = [];

    public static function get_instance(...$args): static
    {
        $called_class = static::class;

        if (!isset(self::$instances[$called_class])) {
            self::$instances[$called_class] = new static(...$args);
        }

        return self::$instances[$called_class];
    }

    // Prevent direct construction and cloning
    protected function __construct() {}
    
    private function __clone() {}

    public function __wakeup(): void
    {
        throw new \Exception("Cannot unserialize a singleton.");
    }
}