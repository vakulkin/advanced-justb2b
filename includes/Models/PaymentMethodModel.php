<?php

namespace JustB2b\Models;

use WC_Payment_Gateway;
use WC_Payment_Gateways;
use JustB2b\Fields\SelectField;
use JustB2b\Fields\SeparatorField;
use JustB2b\Fields\NonNegativeFloatField;
use JustB2b\Controllers\UsersController;
use JustB2b\Traits\RuntimeCacheTrait;

defined('ABSPATH') || exit;

class PaymentMethodModel extends AbstractKeyModel
{
    use RuntimeCacheTrait;

    protected WC_Payment_Gateway $WCMethod;

    public function __construct(WC_Payment_Gateway $WCMethod)
    {
        $this->WCMethod = $WCMethod;
    }

    public function getWCMethod(): WC_Payment_Gateway
    {
        return $this->WCMethod;
    }

    public function getKey(): string
    {
        return $this->getFromRuntimeCache(
            "payment_key_{$this->WCMethod->id}",
            fn() =>
            'temp_payment---' . str_replace(':', '---', $this->WCMethod->id)
        );
    }

    public function getSepKey(): string
    {
        return $this->getFromRuntimeCache(
            "payment_sep_key_{$this->WCMethod->id}",
            fn() =>
            $this->getKey() . '---sep'
        );
    }

    public function getShowKey(): string
    {
        return $this->getFromRuntimeCache(
            "payment_show_key_{$this->WCMethod->id}",
            fn() =>
            $this->getKey() . '---show'
        );
    }

    public function getMinTotalKey(): string
    {
        return $this->getFromRuntimeCache(
            "payment_min_total_key_{$this->WCMethod->id}",
            fn() =>
            $this->getKey() . '---min_total'
        );
    }

    public function getMaxTotalKey(): string
    {
        return $this->getFromRuntimeCache(
            "payment_max_total_key_{$this->WCMethod->id}",
            fn() =>
            $this->getKey() . '---max_total'
        );
    }

    public function getLabel(): string
    {
        return $this->getFromRuntimeCache("payment_label_{$this->WCMethod->id}", fn() => sprintf(
            '%s (%s)',
            $this->WCMethod->get_title(),
            $this->WCMethod->enabled === 'yes' ? 'enabled' : 'disabled'
        ));
    }

    public function isActive(): bool
    {
        return $this->getFromRuntimeCache("payment_is_active_{$this->WCMethod->id}", function () {
            $userController = UsersController::getInstance();
            $currentUser = $userController->getCurrentUser();
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

    public function getMinOrderTotal(): float|false
    {
        return $this->getFromRuntimeCache("payment_min_total_{$this->WCMethod->id}", function () {
            $option = $this->getFieldValue($this->getMinTotalKey());
            return is_numeric($option) ? (float) $option : false;
        });
    }

    public function getMaxOrderTotal(): float|false
    {
        return $this->getFromRuntimeCache("payment_max_total_{$this->WCMethod->id}", function () {
            $option = $this->getFieldValue($this->getMaxTotalKey());
            return is_numeric($option) ? (float) $option : false;
        });
    }

    public function getMethodFields(): array
    {
        return $this->getFromRuntimeCache("payment_method_fields_{$this->WCMethod->id}", function () {
            return [
                new SeparatorField($this->getSepKey(), $this->getLabel()),
                (new SelectField($this->getShowKey(), "Show for users"))
                    ->setOptions([
                        'b2x' => 'b2x',
                        'b2c' => 'b2c',
                        'b2b' => 'b2b',
                    ])
                    ->setWidth(33),
                (new NonNegativeFloatField($this->getMinTotalKey(), 'Min Order Total'))
                    ->setDefaultValue(false)
                    ->setWidth(33),
                (new NonNegativeFloatField($this->getMaxTotalKey(), 'Max Order Total'))
                    ->setDefaultValue(false)
                    ->setWidth(33),
            ];
        });
    }

    public static function getPaymentMethods(): array
    {
        $methods = [];
        $gateways = WC_Payment_Gateways::instance()->payment_gateways();
        foreach ($gateways as $gateway) {
            $methods[$gateway->id] = new self($gateway);
        }
        return $methods;
    }

    public static function getFieldsDefinition(): array
    {
        $fields = [];
        foreach (self::getPaymentMethods() as $method) {
            $fields = array_merge($fields, $method->getMethodFields());
        }
        return $fields;
    }
}
