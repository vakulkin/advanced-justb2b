<?php

namespace JustB2b\Controllers;

defined('ABSPATH') || exit;

use WC_Shipping_Zone;
use WC_Shipping_Zones;

use Carbon_Fields\Container;

use JustB2b\Utils\Prefixer;
use JustB2b\Fields\SelectField;
use JustB2b\Fields\SeparatorField;
use JustB2b\Fields\NonNegativeFloatField;

use JustB2b\Fields\FieldBuilder;
use JustB2b\Fields\Definitions\GlobalFieldsDefinition;


class GlobalController extends BaseController
{
    // protected static string $modelClass = RuleModel::class;
    protected array $shippingMethods = [];
    protected array $shippingFieldsDefinition = [];

    public function __construct() {
        parent::__construct();
        add_action('carbon_after_save_theme_options', [$this, 'removeUnusedShippingFields']);
    }

    protected function init_all_shipping_methods_from_all_zones()
    {
        if (!class_exists('WC_Shipping_Zones')) {
            return;
        }

        // Get all zones including default zone (id = 0)
        $zones = WC_Shipping_Zones::get_zones();
        $zones[] = ['zone_id' => 0]; // Add "Rest of the World"
        error_log(print_r($zones, true));

        foreach ($zones as $zone_data) {
            $zone = new WC_Shipping_Zone($zone_data['zone_id']);
            $zone_name = $zone->get_zone_name();
            $shipping_methods = $zone->get_shipping_methods();

            foreach ($shipping_methods as $method) {
                $instance_id = $method->get_instance_id();
                $key = $method->get_rate_id();
                $label = sprintf('%s: %s — %s', $instance_id, $zone_name, $method->get_title());
                $this->shippingMethods[$key] = $label;
            }
        }
    }

    public function registerFields()
    {
        $this->init_all_shipping_methods_from_all_zones();
        $this->initShippingData();

        $definitions = GlobalFieldsDefinition::getMainFileds();
        $fields = FieldBuilder::buildFields($definitions);

        $baseDefinitions = GlobalFieldsDefinition::getBaseFields();
        $baseFields = FieldBuilder::buildFields($baseDefinitions);

        $shippingFields = FieldBuilder::buildFields($this->shippingFieldsDefinition);

        $b2cDefinitions = GlobalFieldsDefinition::getB2cFileds();
        $b2cFields = FieldBuilder::buildFields($b2cDefinitions); 

        Container::make('theme_options', 'JustB2B')
            ->set_page_file('justb2b-settings')
            ->set_icon('dashicons-admin-generic')
            ->add_tab('Display', $fields)
            ->add_tab('Pricing base', $baseFields)
            ->add_tab('Shipping', $shippingFields)
            ->add_tab('b2c info', $b2cFields);
    }

    // public static function getShippingFields()
    // {
    //     [$fields, $activeKeys] = self::generateShippingData();
    //     self::removeUnusedShippingFields($activeKeys);
    //     return $fields;
    // }

    protected function initShippingData()
    {
        foreach ($this->shippingMethods as $key => $label) {
            $key = "temp---" . str_replace(':', '---', $key);

            $sepKey = "{$key}---sep";
            $this->shippingFieldsDefinition[] = new SeparatorField($sepKey, $label);

            $showKey = "{$key}---show";
            $this->shippingFieldsDefinition[] = (new SelectField($showKey, "Whom to show"))->setOptions([
                'b2x' => 'b2x [all]',
                'b2c' => 'b2c',
                'b2b' => 'b2b',
            ])->setWidth(50);

            $freeKey = "{$key}---free";
            $this->shippingFieldsDefinition[] = (new NonNegativeFloatField($freeKey, 'Free from'))->setWidth(50);
        }
    }

    protected function removeUnusedShippingFields()
    {
        global $wpdb;

        $tempKey = Prefixer::getPrefixedMeta('temp---%');
        $prefixedActiveKeys = [];
        foreach ($this->shippingFieldsDefinition as $field) {
            // !!!! check prefixed keys in other cases
            
            $prefixedActiveKeys[] = $field->getPrefixedKey();
        }

        $prefixedActiveKeysCondition = '';
        if (!empty($prefixedActiveKeys)) {
            $implodedPrefixedActiveKeys = implode("', '", $prefixedActiveKeys);
            $prefixedActiveKeysCondition = "AND option_name NOT IN ('{$implodedPrefixedActiveKeys}')";
        }

        $wpdb->query("DELETE FROM wp_options
            WHERE option_name LIKE '{$tempKey}' {$prefixedActiveKeysCondition}");
    }
}