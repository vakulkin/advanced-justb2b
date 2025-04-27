<?php

namespace JustB2b\Controllers;

defined('ABSPATH') || exit;

use WC_Shipping_Zone;
use WC_Shipping_Zones;
use JustB2b\Utils\Prefixer;
use JustB2b\Models\ShippingMethodModel;
use JustB2b\Fields\FieldBuilder;
use JustB2b\Traits\LazyLoaderTrait;

class ShippingController extends BaseController
{
    use LazyLoaderTrait;

    protected ?array $shippingMethods = null;
    protected ?array $shippingFieldsDefinition = null;

    public function __construct()
    {
        parent::__construct();
        add_filter('woocommerce_package_rates', [$this, 'filterShippingRates'], 10, 2);
    }

    public function registerFields()
    {
        $shippingFields = FieldBuilder::buildFields($this->getShippingFieldsDefinition());

        $globalController = GlobalController::getInstance();
        $generalSettings = $globalController->getGlobalSettings();

        $generalSettings->add_tab('Shipping', $shippingFields);
    }

    public function getShippingMethods(): array
    {
        $this->initShippingMethods();
        return $this->shippingMethods;
    }

    protected function initShippingMethods(): void
    {
        $this->lazyLoad($this->shippingMethods, function () {
            $methods = [];

            if (!class_exists('WC_Shipping_Zones')) {
                return $methods;
            }

            $zones = WC_Shipping_Zones::get_zones();
            $zones[] = ['zone_id' => 0]; // Add "Rest of the World"

            foreach ($zones as $zoneData) {
                $zone = new WC_Shipping_Zone($zoneData['zone_id']);
                foreach ($zone->get_shipping_methods() as $method) {
                    $methods[$method->get_rate_id()] = new ShippingMethodModel($method, $zone);
                }
            }

            return $methods;
        });
    }

    public function getShippingFieldsDefinition(): array
    {
        $this->initShippingFieldsDefinition();
        return $this->shippingFieldsDefinition;
    }

    protected function initShippingFieldsDefinition(): void
    {
        $this->lazyLoad($this->shippingFieldsDefinition, function () {
            $fields = [];

            foreach ($this->getShippingMethods() as $method) {
                $fields = array_merge(
                    $fields,
                    $method->getFields()
                );
            }

            return $fields;
        });
    }

    protected function removeUnusedShippingFields(): void
    {
        global $wpdb;

        $tempKey = Prefixer::getPrefixedMeta('temp---%');
        $prefixedActiveKeys = [];

        foreach ($this->getShippingFieldsDefinition() as $field) {
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

    public function filterShippingRates($rates)
    {
        $shippingMethods = $this->getShippingMethods();

        foreach ($rates as $rate_id => $rate) {
            if (isset($shippingMethods[$rate_id])) {
                $method = $shippingMethods[$rate_id];
                if (!$method->isActive()) {
                    unset($rates[$rate_id]);
                    continue;
                }
                $rates[$rate_id]->cost = $method->getCost();
            }
        }

        return $rates;
    }
}
