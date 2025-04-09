<?php

namespace JustB2b;

use Carbon_Fields\Field\Field;


/*
Plugin Name:  Advanced JustB2B Plugin
Description: A plugin to manage B2B interactions with custom business rules, user roles, product groups, and pricing strategies.
Text Domain: justb2b
*/

defined('ABSPATH') || exit;

require_once __DIR__ . '/vendor/autoload.php';

use Carbon_Fields\Carbon_Fields;
use Carbon_Fields\Container;

use JustB2b\Fields\FieldBuilder;
use JustB2b\Models\ProductModel;

use JustB2b\Traits\SingletonTrait;
use JustB2b\Controllers\LogicBlocksController;
use JustB2b\Controllers\RolesController;
use JustB2b\Controllers\RulesController;
use JustB2b\Controllers\ProductsController;

use JustB2b\Fields\Definitions\GlobalFieldsDefinition;

define('JUSTB2B_PLUGIN_VERSION', '3.0.5');
define('JUSTB2B_PLUGIN_FILE', __FILE__);
define('JUSTB2B_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('JUSTB2B_PLUGIN_URL', plugin_dir_url(__FILE__));


class AdvancedJustB2b
{
    use SingletonTrait;

    public function __construct()
    {
        LogicBlocksController::get_instance();
        RolesController::get_instance();
        RulesController::get_instance();
        ProductsController::get_instance();

        add_action('after_setup_theme', [$this, 'crb_load']);
        add_action('carbon_fields_register_fields', [$this, 'general_settings']);
        add_action('admin_menu', [$this, 'register_submenus'], 100);


        add_action('wp_body_open', [$this, 'display_active_rules_after_header']);
    }

    public function display_active_rules_after_header()
    {
        echo '<pre>';
        $product_103 = new ProductModel(111);
        // var_dump($product_103->getRules());
        var_dump("base net price", $product_103->getPriceDisplay()->getBaseNetPrice());
        var_dump("base gross price", $product_103->getPriceDisplay()->getBaseGrossPrice());
        var_dump("final net price", $product_103->getPriceDisplay()->getFinalNetPrice());
        var_dump("final gross price", $product_103->getPriceDisplay()->getFinalGrossPrice());
        echo $product_103->getPriceDisplay()->getB2BPrices();
        echo '</pre>';
    }

    public function crb_load()
    {
        Carbon_Fields::boot();
    }

    public function general_settings()
    {
        $definitions = GlobalFieldsDefinition::getMainFileds();
        $fields = FieldBuilder::buildFields($definitions);

        Container::make('theme_options', 'JustB2B')
            ->set_page_file('justb2b-settings')
            ->set_icon('dashicons-admin-generic')
            ->add_fields($fields);
    }

    public function register_submenus()
    {
        global $submenu;
        if (isset($submenu['justb2b-settings'][0])) {
            $submenu['justb2b-settings'][0][0] = 'Settings';
        }
    }
}


AdvancedJustB2b::get_instance();
