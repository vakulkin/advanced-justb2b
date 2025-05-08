<?php

namespace JustB2b\Controllers;

defined('ABSPATH') || exit;

use JustB2b\Traits\SingletonTrait;

abstract class BaseController
{
    use SingletonTrait;

    protected function __construct()
    {
        add_action('carbon_fields_register_fields', [$this, 'registerCarbonFields'], 20);
    }

    abstract public function registerCarbonFields();

}