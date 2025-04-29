<?php

namespace JustB2b\Models;

use JustB2b\Utils\Prefixer;
use JustB2b\Controllers\UsersController;
use JustB2b\Utils\Pricing\PriceCalculator;
use JustB2b\Fields\NonNegativeFloatField;
use JustB2b\Fields\SelectField;
use JustB2b\Fields\SeparatorField;
use JustB2b\Traits\LazyLoaderTrait;

defined('ABSPATH') || exit;

class ShippingMethodModel
{
    use LazyLoaderTrait;

    protected ?object $WCMethod = null;
    protected ?object $WCZone = null;

    protected ?string $key = null;
    protected ?string $sepKey = null;
    protected ?string $showKey = null;
    protected ?string $freeKey = null;
    protected null|false|float $freeFrom = null;
    protected ?string $label = null;

    protected ?bool $isActive = null;
    protected ?array $fields = null;

    public function __construct($WCMethod, $WCZone)
    {
        $this->WCMethod = $WCMethod;
        $this->WCZone = $WCZone;
    }

    public function getWCMethod(): object
    {
        return $this->WCMethod;
    }

    public function getWCZone(): object
    {
        return $this->WCZone;
    }

    public function getKey(): string
    {
        $this->initKey();
        return $this->key;
    }

    protected function initKey(): void
    {
        $this->lazyLoad($this->key, function () {
            $rateId = str_replace(':', '---', $this->getWCMethod()->get_rate_id());
            return "temp---{$rateId}";
        });
    }

    public function getSepKey(): string
    {
        $this->initSepKey();
        return $this->sepKey;
    }

    protected function initSepKey(): void
    {
        $this->lazyLoad($this->sepKey, fn() => $this->getKey() . '---sep');
    }

    public function getShowKey(): string
    {
        $this->initShowKey();
        return $this->showKey;
    }

    protected function initShowKey(): void
    {
        $this->lazyLoad($this->showKey, fn() => $this->getKey() . '---show');
    }

    public function getFreeKey(): string
    {
        $this->initFreeKey();
        return $this->freeKey;
    }

    protected function initFreeKey(): void
    {
        $this->lazyLoad($this->freeKey, fn() => $this->getKey() . '---free');
    }

    public function getLabel(): string
    {
        $this->initLabel();
        return $this->label;
    }

    protected function initLabel(): void
    {
        $this->lazyLoad($this->label, function () {
            return sprintf(
                '%s: %s — %s',
                $this->getWCMethod()->get_instance_id(),
                $this->getWCZone()->get_zone_name(),
                $this->getWCMethod()->get_title()
            );
        });
    }

    public function isActive(): bool
    {
        $this->initIsActive();
        return $this->isActive;
    }

    protected function initIsActive(): void
    {
        $this->lazyLoad($this->isActive, function () {
            $userController = UsersController::getInstance();
            $currentUser = $userController->getCurrentUser();

            $show = get_option(Prefixer::getPrefixedMeta($this->getShowKey()));

            if ($show === 'b2b' && !$currentUser->isB2b()) {
                return false;
            }

            if ($show === 'b2c' && $currentUser->isB2b()) {
                return false;
            }

            return true;
        });
    }

    protected function initFreeFrom(): void
    {
        $this->lazyLoad($this->freeFrom, function (): false|float {
            $optionValue = get_option(Prefixer::getPrefixedMeta($this->getFreeKey()));

            if (is_numeric($optionValue)) {
                return PriceCalculator::getFloat($optionValue);
            }

            return false;
        });
    }

    public function getFreeFrom(): false|float
    {
        $this->initFreeFrom();
        return $this->freeFrom;
    }

    public function getFields(): array
    {
        $this->initFields();
        return $this->fields;
    }

    protected function initFields(): void
    {
        $this->lazyLoad($this->fields, function () {
            return [
                new SeparatorField($this->getSepKey(), $this->getLabel()),
                (new SelectField($this->getShowKey(), "Show for users"))
                    ->setOptions([
                        'b2x' => 'b2x [all]',
                        'b2c' => 'b2c',
                        'b2b' => 'b2b',
                    ])
                    ->setWidth(50),
                (new NonNegativeFloatField($this->getFreeKey(), 'Free from order net'))
                    ->setWidth(50),
            ];
        });
    }
}
