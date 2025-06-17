<?php

namespace JustB2b\Controllers\Key;

use Carbon_Fields\Carbon_Fields;
use Carbon_Fields\Container\Container;
use JustB2b\Controllers\Key\AbstractKeyController;
use JustB2b\Fields\FieldBuilder;
use JustB2b\Models\Key\SettingsModel;

defined('ABSPATH') || exit;

class GlobalController extends AbstractKeyController
{
    protected ?Container $globalSettings = null;
    protected SettingsModel $settingsModelObject;

    protected function __construct()
    {
        parent::__construct();

        $this->settingsModelObject = new SettingsModel();

        add_action('init', function () {
            wp_cache_add_non_persistent_groups([ 'justb2b_plugin' ]);
        });

        add_action('after_setup_theme', [ $this, 'crbLoad' ]);
        add_action('admin_menu', [ $this, 'registerSubmenus' ], 100);
        add_action('wp_enqueue_scripts', [ $this, 'enqueueScripts' ]);
        add_action('admin_enqueue_scripts', [ $this, 'adminEnqueueScripts' ]);
    }

    public function getSettingsModelObject()
    {
        return $this->settingsModelObject;
    }

    public function crbLoad()
    {
        if (! did_action('carbon_fields_register_fields')) {
            Carbon_Fields::boot();
        }
        $this->initGlobalSettings();
    }

    protected function initGlobalSettings(): void
    {
        $this->globalSettings = Container::make('theme_options', 'JustB2B')
            ->set_page_file('justb2b-settings')
            ->set_icon('dashicons-admin-generic');

    }

    public function getGlobalSettings(): Container
    {
        return $this->globalSettings;
    }

    public function registerCarbonFields()
    {
        $definitions = SettingsModel::getFieldsDefinition();
        $fields = FieldBuilder::buildFields($definitions);

        $this->getGlobalSettings()
            ->add_tab('Display', $fields)
        ;
    }

    public function registerSubmenus()
    {
        global $submenu;

        if (isset($submenu['justb2b-settings'][0])) {
            $submenu['justb2b-settings'][0][0] = 'Settings';
        }
    }

    public function enqueueScripts()
    {
        wp_enqueue_style(
            'justb2b-frontend',
            JUSTB2B_PLUGIN_URL . 'assets/css/frontend.css',
            [],
            JUSTB2B_PLUGIN_VERSION
        );

        wp_enqueue_script(
            'justb2b-product',
            JUSTB2B_PLUGIN_URL . 'assets/js/price.js',
            [ 'jquery' ],
            JUSTB2B_PLUGIN_VERSION
        );

        wp_localize_script('justb2b-product', 'justb2b', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('justb2b_price_nonce'),
        ]);
    }

    public function adminEnqueueScripts()
    {
        wp_enqueue_style(
            'justb2b-backend',
            JUSTB2B_PLUGIN_URL . 'assets/css/backend.css',
            [],
            JUSTB2B_PLUGIN_VERSION
        );
    }
}
