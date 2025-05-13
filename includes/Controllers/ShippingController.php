<?php

namespace JustB2b\Controllers;

defined('ABSPATH') || exit;

use JustB2b\Traits\RuntimeCacheTrait;
use JustB2b\Utils\Prefixer;
use JustB2b\Models\ShippingMethodModel;
use JustB2b\Fields\FieldBuilder;

class ShippingController extends AbstractKeyController
{
    use RuntimeCacheTrait;

    protected string $modelClass = ShippingMethodModel::class;

    protected function __construct()
    {
        parent::__construct();

        add_filter('woocommerce_package_rates', [$this, 'filterShippingMethods']);
        add_action('woocommerce_checkout_update_order_review', [$this, 'resetShippingCache']);
    }

    public function registerCarbonFields()
    {
        $shippingFields = FieldBuilder::buildFields($this->modelClass::getFieldsDefinition());

        $globalController = GlobalController::getInstance();
        $generalSettings = $globalController->getGlobalSettings();

        $generalSettings->add_tab('Shipping', $shippingFields);
    }

    protected function removeUnusedShippingFields(): void
    {
        global $wpdb;

        $tempKey = Prefixer::getPrefixedMeta('temp_shipping---%');
        $prefixedActiveKeys = [];

        foreach ($this->modelClass::getFieldsDefinition() as $field) {
            $prefixedActiveKeys[] = $field->getPrefixedKey();
        }

        $prefixedActiveKeysCondition = '';
        if (!empty($prefixedActiveKeys)) {
            $imploded = implode("', '", $prefixedActiveKeys);
            $prefixedActiveKeysCondition = "AND option_name NOT IN ('{$imploded}')";
        }

        $wpdb->query("DELETE FROM wp_options WHERE option_name LIKE '{$tempKey}' {$prefixedActiveKeysCondition}");
    }

    public function filterShippingMethods($rates)
    {
        $shippingMethods = $this->getFromRuntimeCache('available_shipping_methods', function () {
            return $this->modelClass::getShippingMethods();
        });

        foreach ($rates as $rate_id => $rate) {
            if (isset($shippingMethods[$rate_id])) {
                $method = $shippingMethods[$rate_id];

                if (!$method->isActive()) {
                    unset($rates[$rate_id]);
                    continue;
                }

                $freeFrom = $method->getFreeFrom();
                $cartSubTotal = WC()->cart->get_subtotal();

                if ($freeFrom !== false && $cartSubTotal > 0 && $cartSubTotal >= $freeFrom) {
                    $rates[$rate_id]->cost = 0.0;
                }
            }
        }

        return $rates;
    }

    public function resetShippingCache()
    {
        if (!function_exists('WC') || !WC()->cart || !WC()->session) {
            return;
        }

        $packages = WC()->cart->get_shipping_packages();
        foreach ($packages as $key => $value) {
            $sessionKey = "shipping_for_package_$key";
            WC()->session->set($sessionKey, null);
        }
    }
}
