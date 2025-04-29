<?php

namespace JustB2b;


/*
Plugin Name:  Advanced JustB2B Plugin
Description: A plugin to manage B2B interactions with custom business rules, user roles, product groups, and pricing strategies.
Text Domain: justb2b
*/

defined('ABSPATH') || exit;

require_once __DIR__ . '/vendor/autoload.php';

use JustB2b\Traits\SingletonTrait;
use JustB2b\Controllers\GlobalController;
use JustB2b\Controllers\ShippingController;
use JustB2b\Controllers\RolesController;
use JustB2b\Controllers\RulesController;
use JustB2b\Controllers\ProductsController;
use JustB2b\Controllers\UsersController;
use JustB2b\Integrations\WoodMartIntegration;
use JustB2b\Integrations\WCProductTableLitePro;


define('JUSTB2B_PLUGIN_VERSION', '3.0.5');
define('JUSTB2B_PLUGIN_FILE', __FILE__);
define('JUSTB2B_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('JUSTB2B_PLUGIN_URL', plugin_dir_url(__FILE__));


class AdvancedJustB2b
{
    use SingletonTrait;

    public function __construct()
    {
        GlobalController::getInstance();
        ShippingController::getInstance();
        RolesController::getInstance();
        RulesController::getInstance();
        ProductsController::getInstance();
        UsersController::getInstance();
        WoodMartIntegration::getInstance();
        WCProductTableLitePro::getInstance();
    }
}


AdvancedJustB2b::getInstance();
