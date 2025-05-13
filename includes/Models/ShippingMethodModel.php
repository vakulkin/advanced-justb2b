<?php

namespace JustB2b\Models;

use WC_Product_Simple;
use WC_Shipping_Method;
use WC_Shipping_Zone;
use WC_Shipping_Zones;
use JustB2b\Controllers\UsersController;
use JustB2b\Utils\Pricing\PriceCalculator;
use JustB2b\Fields\NonNegativeFloatField;
use JustB2b\Fields\SelectField;
use JustB2b\Fields\SeparatorField;
use JustB2b\Traits\RuntimeCacheTrait;

defined('ABSPATH') || exit;

class ShippingMethodModel extends AbstractKeyModel
{
    use RuntimeCacheTrait;

    protected WC_Shipping_Method $WCMethod;
    protected WC_Shipping_Zone $WCZone;

    public function __construct(WC_Shipping_Method $WCMethod, WC_Shipping_Zone $WCZone)
    {
        $this->WCMethod = $WCMethod;
        $this->WCZone = $WCZone;
    }

    public function getWCMethod(): WC_Shipping_Method
    {
        return $this->WCMethod;
    }

    public function getWCZone(): WC_Shipping_Zone
    {
        return $this->WCZone;
    }

    public function getKey(): string
    {
        return $this->getFromRuntimeCache(
            "shipping_key_{$this->WCMethod->get_rate_id()}",
            fn() =>
            "temp_shipping---" . str_replace(':', '---', $this->WCMethod->get_rate_id())
        );
    }

    public function getSepKey(): string
    {
        return $this->getFromRuntimeCache(
            "shipping_sep_key_{$this->WCMethod->get_rate_id()}",
            fn() =>
            $this->getKey() . '---sep'
        );
    }

    public function getShowKey(): string
    {
        return $this->getFromRuntimeCache(
            "shipping_show_key_{$this->WCMethod->get_rate_id()}",
            fn() =>
            $this->getKey() . '---show'
        );
    }

    public function getFreeKey(): string
    {
        return $this->getFromRuntimeCache(
            "shipping_free_key_{$this->WCMethod->get_rate_id()}",
            fn() =>
            $this->getKey() . '---free'
        );
    }

    public function getLabel(): string
    {
        return $this->getFromRuntimeCache("shipping_label_{$this->WCMethod->get_rate_id()}", function () {
            $status = $this->WCMethod->enabled === 'yes' ? 'enabled' : 'disabled';
            return sprintf(
                '%s: %s — %s (%s)',
                $this->WCMethod->get_instance_id(),
                $this->WCZone->get_zone_name(),
                $this->WCMethod->get_title(),
                $status
            );
        });
    }

    public function isActive(): bool
    {
        return $this->getFromRuntimeCache("shipping_is_active_{$this->WCMethod->get_rate_id()}", function () {
            $currentUser = UsersController::getInstance()->getCurrentUser();
            $show = $this->getFieldValue($this->getShowKey());

            if ($show === 'b2b' && !$currentUser->isB2b()) {
                return false;
            }

            if ($show === 'b2c' && $currentUser->isB2b()) {
                return false;
            }

            return true;
        });
    }

    public function getFreeFrom(): false|float
    {
        return $this->getFromRuntimeCache("shipping_free_from_{$this->WCMethod->get_rate_id()}", function () {
            $optionValue = $this->getFieldValue($this->getFreeKey());
            return is_numeric($optionValue) ? PriceCalculator::getFloat($optionValue) : false;
        });
    }

    public function getMethodFields(): array
    {
        return $this->getFromRuntimeCache("shipping_fields_{$this->WCMethod->get_rate_id()}", function () {
            return [
                new SeparatorField($this->getSepKey(), $this->getLabel()),
                (new SelectField($this->getShowKey(), "Show for users"))
                    ->setOptions([
                        'b2x' => 'b2x',
                        'b2c' => 'b2c',
                        'b2b' => 'b2b',
                    ])
                    ->setWidth(50),
                (new NonNegativeFloatField($this->getFreeKey(), 'Free from order net'))
                    ->setDefaultValue(false)
                    ->setWidth(50),
            ];
        });
    }

    public static function getShippingMethods(): array
    {
        $methods = [];

        if (!class_exists('WC_Shipping_Zones')) {
            return $methods;
        }

        $zones = WC_Shipping_Zones::get_zones();
        $zones[] = ['zone_id' => 0]; // include "Rest of the world"

        foreach ($zones as $zoneData) {
            $zone = new WC_Shipping_Zone($zoneData['zone_id']);
            foreach ($zone->get_shipping_methods() as $method) {
                $methods[$method->get_rate_id()] = new self($method, $zone);
            }
        }

        return $methods;
    }

    public static function getFieldsDefinition(): array
    {
        $fields = [];
        foreach (self::getShippingMethods() as $method) {
            $fields = array_merge($fields, $method->getMethodFields());
        }
        return $fields;
    }

    public static function getMethodWithMinimalFreeFrom(): ShippingMethodModel|false
    {
        if (!function_exists('WC') || !WC()->shipping) {
            return false;
        }

        $packages = self::getShippingPackagesForFakeProduct();
        if (empty($packages)) {
            return false;
        }

        $zone = WC_Shipping_Zones::get_zone_matching_package($packages[0]);
        if (!$zone) {
            return false;
        }

        $availableRates = [];

        foreach ($zone->get_shipping_methods(true) as $method) {
            if ($method->enabled === 'yes') {
                $instance = clone $method;
                $instance->calculate_shipping($packages[0]);

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
        $shippingMethods = self::getShippingMethods();

        foreach ($availableRates as $rateId => $rateObject) {
            if (!isset($shippingMethods[$rateId])) {
                continue;
            }

            $method = $shippingMethods[$rateId];
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
    }

    public static function getShippingPackagesForFakeProduct(): array
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
}
