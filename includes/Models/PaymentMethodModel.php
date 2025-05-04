<?php

namespace JustB2b\Models;

use WC_Payment_Gateway;
use JustB2b\Traits\LazyLoaderTrait;
use JustB2b\Fields\SelectField;
use JustB2b\Fields\SeparatorField;
use JustB2b\Fields\NonNegativeFloatField;
use JustB2b\Utils\Prefixer;
use JustB2b\Controllers\UsersController;

defined('ABSPATH') || exit;

class PaymentMethodModel
{
    use LazyLoaderTrait;
    protected ?WC_Payment_Gateway $WCMethod = null;
    protected ?string $key = null;
    protected ?string $label = null;
    protected ?bool $isActive = null;
    protected ?array $fields = null;
    protected null|false|float $minOrderTotal = null;
    protected null|false|float $maxOrderTotal = null;

    public function __construct($WCMethod)
    {
        $this->WCMethod = $WCMethod;
    }

    public function getWCMethod(): WC_Payment_Gateway {
        return $this->WCMethod;
    }

    public function getKey(): string
    {
        $this->lazyLoad($this->key, function () {
            return 'temp_payment---' . str_replace(':', '---', $this->getWCMethod()->id);
        });

        return $this->key;
    }

    public function getLabel(): string
    {
        $this->lazyLoad($this->label, function () {
            $status = $this->getWCMethod()->enabled === 'yes' ? 'enabled' : 'disabled';
            return sprintf('%s (%s)', $this->getWCMethod()->get_title(), $status);
        });

        return $this->label;
    }

    public function isActive(): bool
    {
        $this->lazyLoad($this->isActive, function () {
            $userController = UsersController::getInstance();
            $currentUser = $userController->getCurrentUser();

            $show = get_option(Prefixer::getPrefixedMeta($this->getKey() . '---show'));

            if ($show === 'b2b' && !$currentUser->isB2b()) {
                return false;
            }

            if ($show === 'b2c' && $currentUser->isB2b()) {
                return false;
            }

            return true;
        });

        return $this->isActive;
    }

    public function getMinOrderTotal(): false|float
    {
        $this->lazyLoad($this->minOrderTotal, function () {
            $option = get_option(Prefixer::getPrefixedMeta($this->getKey() . '---min_total'));
            return is_numeric($option) ? (float) $option : false;
        });

        return $this->minOrderTotal;
    }

    public function getMaxOrderTotal(): false|float
    {
        $this->lazyLoad($this->maxOrderTotal, function () {
            $option = get_option(Prefixer::getPrefixedMeta($this->getKey() . '---max_total'));
            return is_numeric($option) ? (float) $option : false;
        });

        return $this->maxOrderTotal;
    }

    public function getFields(): array
    {
        $this->lazyLoad($this->fields, function () {
            return [
                new SeparatorField($this->getKey() . '---sep', $this->getLabel()),
                (new SelectField($this->getKey() . '---show', "Show for users"))
                    ->setOptions([
                        'b2x' => 'b2x',
                        'b2c' => 'b2c',
                        'b2b' => 'b2b',
                    ])
                    ->setWidth(33),
                (new NonNegativeFloatField($this->getKey() . '---min_total', 'Min Order Total'))
                    ->setWidth(33),
                (new NonNegativeFloatField($this->getKey() . '---max_total', 'Max Order Total'))
                    ->setWidth(33),
            ];
        });

        return $this->fields;
    }
}
