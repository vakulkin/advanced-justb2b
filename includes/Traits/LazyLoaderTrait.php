<?php

namespace JustB2b\Traits;

defined('ABSPATH') || exit;

trait LazyLoaderTrait
{
    protected function lazyLoad(&$property, callable $initializer)
    {
        if ($property === null) {
            $property = $initializer();
        }
    }
}
