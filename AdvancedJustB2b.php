<?php

namespace JustB2b;

use JustB2b\Controllers\Key\CartController;
use JustB2b\Controllers\Key\GlobalController;
use JustB2b\Controllers\Key\PaymentController;
use JustB2b\Controllers\Id\ProductsController;
use JustB2b\Controllers\Id\RolesController;
use JustB2b\Controllers\Id\RulesController;
use JustB2b\Controllers\Key\ShippingController;
use JustB2b\Controllers\Id\UsersController;
use JustB2b\Integrations\WCProductTableLitePro;
use JustB2b\Integrations\WoodMartIntegration;
use JustB2b\Traits\SingletonTrait;

defined('ABSPATH') || exit;

require_once __DIR__ . '/vendor/autoload.php';

define('JUSTB2B_PLUGIN_VERSION', '3.0.5');
define('JUSTB2B_PLUGIN_FILE', __FILE__);
define('JUSTB2B_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('JUSTB2B_PLUGIN_URL', plugin_dir_url(__FILE__));

class AdvancedJustB2b
{
    use SingletonTrait;

    public function __construct()
    {
        $this->bootControllers();
        $this->bootIntegrations();
    }

    protected function bootControllers(): void
    {
        GlobalController::getInstance();
        ShippingController::getInstance();
        PaymentController::getInstance();
        CartController::getInstance();
        RolesController::getInstance();
        RulesController::getInstance();
        ProductsController::getInstance();
        UsersController::getInstance();
    }

    protected function bootIntegrations(): void
    {
        WoodMartIntegration::getInstance();
        WCProductTableLitePro::getInstance();
    }
}

AdvancedJustB2b::getInstance();
