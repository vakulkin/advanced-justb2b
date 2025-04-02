<?php

namespace JustB2b\Controllers;

defined('ABSPATH') || exit;

use JustB2b\Traits\SingletonTrait;


abstract class BaseController
{
    use SingletonTrait;

    protected static string $modelClass;

    public function __construct()
    {
        add_action('carbon_fields_register_fields', [$this, 'registerFields'], 20);
    }

    abstract public function registerFields();

}