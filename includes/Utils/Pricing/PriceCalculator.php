<?php

namespace JustB2b\Utils\Pricing;


defined('ABSPATH') || exit;

use JustB2b\Models\RuleModel;

class PriceCalculator
{
    protected float $startPrice;
    protected string $kind;
    protected float $value;
    protected int $finalPrice;
    protected bool $requestPrice;
    protected bool $hideProduct;


    public function __construct(int $startPrice, string $kind, float $value)
    {
        $this->startPrice = abs($startPrice);
        $this->kind = $kind;
        $this->value = abs($value);
        $this->initFinalPrice();
        $this->initRequestPrice();
        $this->initHideProduct();
    }

    protected function initFinalPrice()
    {
        switch ($this->kind) {
            case 'start_price':
                $this->finalPrice = $this->startPrice;
                break;
            case 'minus_percent':
                $this->finalPrice = max(0, $this->startPrice - $this->startPrice * $this->value * 0.01);
                break;
            case 'plus_percent':
                $this->finalPrice = $this->startPrice + $this->startPrice * $this->value * 0.01;
                break;
            case 'minus_number':
                $this->finalPrice = max(0, $this->startPrice - $this->value);
                break;
            case 'plus_number':
                $this->finalPrice = $this->startPrice + $this->value;
                break;
            case 'equals_number':
                $this->finalPrice = $this->value;
                break;
        }
    }

    public function getFinalPrice()
    {
        return $this->finalPrice;
    }

    protected function initRequestPrice()
    {
        $this->requestPrice = $this->kind === 'request_price';
    }

    protected function initHideProduct()
    {
        $this->hideProduct = $this->kind === 'hide_product';
    }

}