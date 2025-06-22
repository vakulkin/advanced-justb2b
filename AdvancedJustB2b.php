<?php

namespace JustB2b;

/*
Plugin Name:  Advanced B2B | JustB2B
Description: The plugin to manage B2B interactions with custom business rules, user roles, product groups, and pricing strategies.
Text Domain: justb2b
*/

use JustB2b\Controllers\Id\SettingsController;
use JustB2b\Controllers\Key\CartController;
use JustB2b\Controllers\Key\CheckoutController;
use JustB2b\Controllers\Key\GlobalController;
use JustB2b\Controllers\Key\PaymentController;
use JustB2b\Controllers\Id\ProductsController;
use JustB2b\Controllers\Id\RolesController;
use JustB2b\Controllers\Id\RulesController;
use JustB2b\Controllers\Key\ShippingController;
use JustB2b\Controllers\Id\UsersController;
use JustB2b\Integrations\WCMLIntegration;
use JustB2b\Integrations\WPMLIntegration;
use JustB2b\Integrations\WCProductTableLitePro;
use JustB2b\Integrations\WoodMartIntegration;
use JustB2b\Integrations\WPBakeryIntegration;
use JustB2b\Shortcodes\FeatureShortcodes;
use JustB2b\Traits\SingletonTrait;

defined( 'ABSPATH' ) || exit;

define( 'JUSTB2B_PLUGIN_VERSION', '3.0.5' );
define( 'JUSTB2B_PLUGIN_FILE', __FILE__ );
define( 'JUSTB2B_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'JUSTB2B_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

require_once JUSTB2B_PLUGIN_DIR . '/vendor/autoload.php';

class AdvancedJustB2b {
	use SingletonTrait;

	public function __construct() {
		add_action( 'init', function () {
			if ( class_exists( 'WooCommerce' ) && function_exists( 'acf' ) ) {
				$this->bootControllers();
				$this->bootIntegrations();
			}
		} );
	}

	protected function bootControllers(): void {
		GlobalController::getInstance();
		ShippingController::getInstance();
		PaymentController::getInstance();
		CartController::getInstance();
		CheckoutController::getInstance();
		RolesController::getInstance();
		RulesController::getInstance();
		ProductsController::getInstance();
		UsersController::getInstance();
		FeatureShortcodes::getInstance();
		SettingsController::getInstance();
	}

	protected function bootIntegrations(): void {
		WCMLIntegration::getInstance();
		WPMLIntegration::getInstance();
		WoodMartIntegration::getInstance();
		WCProductTableLitePro::getInstance();
		WPBakeryIntegration::getInstance();
	}
}

AdvancedJustB2b::getInstance();
