<?php

namespace JustB2b\Controllers;

defined('ABSPATH') || exit;

use WC_Product_Simple;
use WC_Shipping_Zone;
use WC_Shipping_Zones;
use JustB2b\Traits\LazyLoaderTrait;
use JustB2b\Utils\Prefixer;
use JustB2b\Models\ShippingMethodModel;
use JustB2b\Fields\FieldBuilder;

class ShippingController extends BaseController
{
    use LazyLoaderTrait;

    protected ?array $shippingMethods = null;
    protected ?array $shippingFieldsDefinition = null;
    protected ShippingMethodModel|false|null $minimalFreeFromMethod = null;

    protected function __construct()
    {
        parent::__construct();

        add_filter('woocommerce_package_rates', [$this, 'filterShippingMethods']);
        add_action('woocommerce_checkout_update_order_review', [$this, 'resetShippingCache']);
    }

    public function registerCarbonFields()
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
            $zones[] = ['zone_id' => 0];

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
                $fields = array_merge($fields, $method->getFields());
            }

            return $fields;
        });
    }

    protected function removeUnusedShippingFields(): void
    {
        global $wpdb;

        $tempKey = Prefixer::getPrefixedMeta('temp_shipping---%');
        $prefixedActiveKeys = [];

        foreach ($this->getShippingFieldsDefinition() as $field) {
            $prefixedActiveKeys[] = $field->getPrefixedKey();
        }

        $prefixedActiveKeysCondition = '';
        if (!empty($prefixedActiveKeys)) {
            $imploded = implode("', '", $prefixedActiveKeys);
            $prefixedActiveKeysCondition = "AND option_name NOT IN ('{$imploded}')";
        }

        $wpdb->query("DELETE FROM wp_options WHERE option_name LIKE '{$tempKey}' {$prefixedActiveKeysCondition}");
    }

    public function getMethodWithMinimalFreeFrom(): ShippingMethodModel|false
    {
        $this->initMethodWithMinimalFreeFrom();
        return $this->minimalFreeFromMethod;
    }

    protected function initMethodWithMinimalFreeFrom(): void
    {
        $this->lazyLoad($this->minimalFreeFromMethod, function () {
            if (!function_exists('WC') || !WC()->shipping) {
                return false;
            }

            $packages = $this->getShippingPackagesForFakeProduct();

            if (empty($packages)) {
                return false;
            }

            $package = $packages[0];
            $zone = WC_Shipping_Zones::get_zone_matching_package($package);

            if (!$zone) {
                return false;
            }

            $availableRates = [];

            foreach ($zone->get_shipping_methods(true) as $method) {
                if ($method->enabled === 'yes') {
                    $instance = clone $method;
                    $instance->calculate_shipping($package);

                    foreach ($instance->rates as $rateId => $rateObject) {
                        $availableRates[$rateId] = $rateObject;
                    }
                }
            }

            if (empty($availableRates)) {
                return false;
            }

            $bestMethod = null;
            $smallestFreeFrom = null;

            foreach ($availableRates as $rateId => $rateObject) {
                if (!isset($this->shippingMethods[$rateId])) {
                    continue;
                }

                $method = $this->shippingMethods[$rateId];

                if (!$method->isActive()) {
                    continue;
                }

                $freeFrom = $method->getFreeFrom();

                if ($freeFrom === false) {
                    continue;
                }

                if ($smallestFreeFrom === null || $freeFrom < $smallestFreeFrom) {
                    $smallestFreeFrom = $freeFrom;
                    $bestMethod = $method;
                }
            }

            return $bestMethod ?: false;
        });
    }

    public function getShippingPackagesForFakeProduct(): array
    {
        $product = new WC_Product_Simple();
        $product->set_id(0);
        $product->set_name('Fake Product');
        $product->set_price(0.01);
        $product->set_regular_price(0.01);
        $product->set_weight('');
        $product->set_shipping_class_id(0);

        $cartItem = [
            'key' => 'fake_product_' . uniqid(),
            'product_id' => 0,
            'variation_id' => 0,
            'variation' => [],
            'quantity' => 1,
            'data' => $product,
            'data_hash' => md5(serialize($product)),
            'line_tax_data' => ['subtotal' => [], 'total' => []],
        ];

        $package = [
            'contents' => [$cartItem],
            'contents_cost' => 0.01,
            'applied_coupons' => [],
            'destination' => [
                'country' => WC()->customer->get_shipping_country(),
                'state' => WC()->customer->get_shipping_state(),
                'postcode' => WC()->customer->get_shipping_postcode(),
                'city' => WC()->customer->get_shipping_city(),
                'address' => WC()->customer->get_shipping_address(),
                'address_2' => WC()->customer->get_shipping_address_2(),
            ],
        ];

        return [$package];
    }

    public function filterShippingMethods($rates)
    {
        $shippingMethods = $this->getShippingMethods();

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
