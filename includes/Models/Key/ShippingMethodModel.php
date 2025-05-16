<?php

namespace JustB2b\Models\Key;

use WC_Shipping_Method;
use WC_Shipping_Zone;
use JustB2b\Controllers\Key\ShippingController;
use JustB2b\Controllers\Id\UsersController;
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

    protected function cacheContext(array $extra = []): array
    {
        return array_merge(
            parent::cacheContext(),
            ['rate_id' => $this->WCMethod->get_rate_id()]
        );
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
        return self::getFromRuntimeCache(
            fn () => 'temp_shipping---' . str_replace(':', '---', $this->WCMethod->get_rate_id()),
            $this->cacheContext()
        );
    }

    public function getSepKey(): string
    {
        return $this->getKey() . '---sep';
    }
    public function getShowKey(): string
    {
        return $this->getKey() . '---show';
    }
    public function getFreeKey(): string
    {
        return $this->getKey() . '---free';
    }

    public function getLabel(): string
    {
        return self::getFromRuntimeCache(function () {
            $status = $this->WCMethod->enabled === 'yes' ? 'enabled' : 'disabled';
            return sprintf(
                '%s: %s — %s (%s)',
                $this->WCMethod->get_instance_id(),
                $this->WCZone->get_zone_name(),
                $this->WCMethod->get_title(),
                $status
            );
        }, $this->cacheContext());
    }

    public function isActive(): bool
    {
        return self::getFromRuntimeCache(function () {
            $currentUser = UsersController::getInstance()->getCurrentUser();
            $show = $this->getFieldValue($this->getShowKey());

            return !(
                ($show === 'b2b' && !$currentUser->isB2b()) ||
                ($show === 'b2c' && $currentUser->isB2b())
            );
        }, $this->cacheContext() + ['user_id' => get_current_user_id()]);
    }

    public function getFreeFrom(): float|false
    {
        return self::getFromRuntimeCache(function () {
            $value = $this->getFieldValue($this->getFreeKey());
            return is_numeric($value) ? $value : false;
        }, $this->cacheContext());
    }

    public function getFields(): array
    {
        return self::getFromRuntimeCache(function () {
            return [
                new SeparatorField($this->getSepKey(), $this->getLabel()),
                (new SelectField($this->getShowKey(), 'Show for users'))
                    ->setOptions(['b2x' => 'b2x', 'b2c' => 'b2c', 'b2b' => 'b2b'])
                    ->setWidth(50),
                (new NonNegativeFloatField($this->getFreeKey(), 'Free from order net'))
                    ->setDefaultValue(false)
                    ->setWidth(50),
            ];
        }, $this->cacheContext());
    }

    public static function getFieldsDefinition(): array
    {
        $fields = [];
        $shippingController = ShippingController::getInstance();
        $shippingMethods = $shippingController->getShippingMethods();
        foreach ($shippingMethods as $method) {
            $fields = array_merge($fields, $method->getFields());
        }
        return $fields;
    }

}
