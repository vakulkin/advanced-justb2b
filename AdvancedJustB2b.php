<?php

namespace JustB2b;

/*
Plugin Name:  Advanced JustB2B Plugin
Description: A plugin to manage B2B interactions with custom business rules, user roles, product groups, and pricing strategies.
Text Domain: justb2b
*/

defined('ABSPATH') || exit;

require_once __DIR__ . '/vendor/autoload.php';

use Carbon_Fields\Carbon_Fields;

use JustB2b\Traits\SingletonTrait;
use JustB2b\Controllers\GlobalController;
use JustB2b\Controllers\RolesController;
use JustB2b\Controllers\RulesController;
use JustB2b\Controllers\ProductsController;
use JustB2b\Controllers\UsersController;

define('JUSTB2B_PLUGIN_VERSION', '3.0.5');
define('JUSTB2B_PLUGIN_FILE', __FILE__);
define('JUSTB2B_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('JUSTB2B_PLUGIN_URL', plugin_dir_url(__FILE__));


class AdvancedJustB2b
{
    use SingletonTrait;

    public function __construct()
    {
        GlobalController::get_instance();
        RolesController::get_instance();
        RulesController::get_instance();
        ProductsController::get_instance();
        UsersController::get_instance();

        add_action('after_setup_theme', [$this, 'crb_load']);
        add_action('admin_menu', [$this, 'register_submenus'], 100);
    }

    public function crb_load()
    {
        Carbon_Fields::boot();
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
