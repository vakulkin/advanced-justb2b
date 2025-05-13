<?php

namespace JustB2b\Controllers;

use JustB2b\Traits\SingletonTrait;

defined('ABSPATH') || exit;


abstract class AbstractController
{
    use SingletonTrait;

    protected string $modelClass;

    protected function __construct()
    {
        add_action('carbon_fields_register_fields', [$this, 'registerCarbonFields'], 20);
    }

    abstract public function registerCarbonFields();
}
