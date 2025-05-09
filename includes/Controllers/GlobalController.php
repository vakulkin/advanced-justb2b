<?php

namespace JustB2b\Controllers;

defined('ABSPATH') || exit;

use Carbon_Fields\Carbon_Fields;
use Carbon_Fields\Container\Container;
use JustB2b\Fields\RichText;
use JustB2b\Fields\SeparatorField;
use JustB2b\Fields\TextField;
use JustB2b\Fields\SelectField;
use JustB2b\Fields\FieldBuilder;
use JustB2b\Traits\LazyLoaderTrait;

class GlobalController extends BaseController
{
    use LazyLoaderTrait;

    protected ?Container $globalSettings = null;

    protected function __construct()
    {
        parent::__construct();

        add_action('after_setup_theme', [$this, 'crbLoad']);
        add_action('admin_menu', [$this, 'registerSubmenus'], 100);
        add_action('wp_enqueue_scripts', [$this, 'enqueueScripts']);
        add_action('admin_enqueue_scripts', [$this, 'adminEnqueueScripts']);
    }

    public function crbLoad()
    {
        Carbon_Fields::boot();
    }

    protected function initGlobalSettings(): void
    {
        $this->lazyLoad($this->globalSettings, function () {
            return Container::make('theme_options', 'JustB2B')
                ->set_page_file('justb2b-settings')
                ->set_icon('dashicons-admin-generic');
        });
    }

    public function getGlobalSettings(): Container
    {
        $this->initGlobalSettings();
        return $this->globalSettings;
    }

    public function registerCarbonFields()
    {
        $definitions = self::getMainFields();
        $fields = FieldBuilder::buildFields($definitions);

        $baseDefinitions = self::getBaseFields();
        $baseFields = FieldBuilder::buildFields($baseDefinitions);

        $b2cDefinitions = self::getB2cFields();
        $b2cFields = FieldBuilder::buildFields($b2cDefinitions);

        $this->getGlobalSettings()
            ->add_tab('Display', $fields)
            ->add_tab('Pricing base', $baseFields)
            ->add_tab('HTML', $b2cFields);
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
            ['jquery'],
            JUSTB2B_PLUGIN_VERSION
        );

        wp_localize_script('justb2b-product', 'justb2b', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('justb2b_price_nonce')
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

    public static function getBaseFields(): array
    {
        $fieldsData = [
            ['key' => 'rrp_price', 'label' => 'RRP'],
            ['key' => 'base_price_1', 'label' => 'Base price 1'],
            ['key' => 'base_price_2', 'label' => 'Base price 2'],
            ['key' => 'base_price_3', 'label' => 'Base price 3'],
            ['key' => 'base_price_4', 'label' => 'Base price 4'],
            ['key' => 'base_price_5', 'label' => 'Base price 5'],
        ];
        $fieldsDefinition = [];
        foreach ($fieldsData as $field) {
            $key = $field['key'];
            $label = $field['label'];

            $fieldsDefinition[] = (new SelectField($key, $label))
                ->setOptions([
                    'net' => 'net',
                    'gross' => 'gross',
                ])
                ->setWidth(50);
        }
        return $fieldsDefinition;
    }

    public static function getMainFields(): array
    {
        $fieldsData = [
            ['key' => 'base_net', 'label' => 'Base Net'],
            ['key' => 'base_gross', 'label' => 'Base Gross'],
            ['key' => 'final_net', 'label' => 'Final Net'],
            ['key' => 'final_gross', 'label' => 'Final Gross'],
            ['key' => 'rrp_net', 'label' => 'RRP Net'],
            ['key' => 'rrp_gross', 'label' => 'RRP Gross'],
            ['key' => 'qty_table', 'label' => 'Qty Table'],
        ];

        $showOptions = [
            'show' => 'show',
            'hide' => 'hide',
            'only_product' => 'only_product',
            'only_loop' => 'only_loop',
        ];

        $fieldsDefinition = [];
        foreach ($fieldsData as $field) {
            $key = $field['key'];

            $sepKey = "sep_{$key}";
            $fieldsDefinition[] = (new SeparatorField($sepKey, $field['label']));

            foreach (['single', 'loop'] as $place) {
                foreach (['b2c', 'b2b'] as $kind) {
                    foreach (['prefix', 'postfix'] as $position) {
                        $finalKey = "{$place}_{$kind}_{$key}_{$position}";
                        $fieldsDefinition[] = (new TextField($finalKey, "{$kind} {$place} {$position}"))->setWidth(25);
                    }
                }
            }

            foreach (['b2c', 'b2b'] as $kind) {
                $typeKey = "{$kind}_{$key}";
                $fieldsDefinition[] = (new SelectField($typeKey, "{$kind} visibility"))
                    ->setOptions($showOptions)
                    ->setWidth(50);
            }
        }

        return $fieldsDefinition;
    }

    public static function getB2cFields(): array
    {
        $fieldsDefinition = [];
        $types = ['b2c', 'b2b'];

        foreach ($types as $type) {
            $fieldsDefinition[] = (new SelectField("show_{$type}_html_1", "show_{$type}_html_1"))
                ->setOptions([
                    'show' => 'show',
                    'hide' => 'hide',
                ])
                ->setWidth(100);
            $fieldsDefinition[] = (new RichText("{$type}_html_1", "{$type}_html_1"))
                ->setWidth(100);
        }

        return $fieldsDefinition;
    }
}
