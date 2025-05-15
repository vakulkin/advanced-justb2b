<?php

namespace JustB2b\Models\Key;

use WC_Payment_Gateway;
use JustB2b\Controllers\Key\PaymentController;
use JustB2b\Fields\SelectField;
use JustB2b\Fields\SeparatorField;
use JustB2b\Fields\NonNegativeFloatField;
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

    protected function cacheContext(array $extra = []): array
    {
        return array_merge([
            parent::cacheContext(),
            'method_id' => $this->WCMethod->id
        ]);
    }

    public function getWCMethod(): WC_Payment_Gateway
    {
        return $this->WCMethod;
    }

    public function getKey(): string
    {
        return self::getFromRuntimeCache(
            fn () => 'temp_payment---' . str_replace(':', '---', $this->WCMethod->id),
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

    public function getMinTotalKey(): string
    {
        return $this->getKey() . '---min_total';
    }

    public function getMaxTotalKey(): string
    {
        return $this->getKey() . '---max_total';
    }

    public function getLabel(): string
    {
        return self::getFromRuntimeCache(
            fn () => sprintf(
                '%s (%s)',
                $this->WCMethod->get_title(),
                $this->WCMethod->enabled === 'yes' ? 'enabled' : 'disabled'
            ),
            $this->cacheContext()
        );
    }

    public function isActive(): bool
    {
        return self::getFromRuntimeCache(function () {
            $userController = \JustB2b\Controllers\Id\UsersController::getInstance();
            $currentUser = $userController->getCurrentUser();
            $show = $this->getFieldValue($this->getShowKey());

            if ($show === 'b2b' && !$currentUser->isB2b()) {
                return false;
            }

            if ($show === 'b2c' && $currentUser->isB2b()) {
                return false;
            }

            return true;
        }, $this->cacheContext(['user_id' => get_current_user_id()]));
    }

    public function getMinOrderTotal(): float|false
    {
        return self::getFromRuntimeCache(function () {
            $option = $this->getFieldValue($this->getMinTotalKey());
            return is_numeric($option) ? (float) $option : false;
        }, $this->cacheContext());
    }

    public function getMaxOrderTotal(): float|false
    {
        return self::getFromRuntimeCache(function () {
            $option = $this->getFieldValue($this->getMaxTotalKey());
            return is_numeric($option) ? (float) $option : false;
        }, $this->cacheContext());
    }


    public function getFields(): array
    {
        return self::getFromRuntimeCache(function () {
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
        }, $this->cacheContext());
    }

    public static function getFieldsDefinition(): array
    {
        $fields = [];
        $paymentController = PaymentController::getInstance();
        foreach ($paymentController->getPaymentMethods() as $method) {
            $fields = array_merge($fields, $method->getFields());
        }
        return $fields;
    }
}
