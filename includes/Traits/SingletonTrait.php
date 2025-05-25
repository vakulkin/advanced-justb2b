<?php

namespace JustB2b\Traits;

defined('ABSPATH') || exit;

trait SingletonTrait
{
    /**
     * @feature-section architecture
     * @title[ru] Надёжность и стабильность архитектуры
     * @desc[ru] JustB2B построен по современным стандартам программирования. Это означает высокую надёжность, стабильную работу и уверенность в том, что всё будет работать как надо — даже при большом количестве пользователей и товаров.
     * @order 50
     */

    /**
     * @feature architecture singleton_trait
     * @title[ru] Умная экономия ресурсов
     * @desc[ru] В плагине каждый важный компонент создаётся только один раз — это ускоряет работу сайта и снижает нагрузку на сервер. Всё работает быстро и без лишних затрат.
     * @order 51
     */

    private static array $instances = [];

    public static function getInstance(...$args): static
    {
        $called_class = static::class;

        if (!isset(self::$instances[$called_class])) {
            self::$instances[$called_class] = new static(...$args);
        }

        return self::$instances[$called_class];
    }

    // Prevent direct construction and cloning
    protected function __construct()
    {
    }

    private function __clone()
    {
    }

    public function __wakeup(): void
    {
        throw new \Exception("Cannot unserialize a singleton.");
    }
}