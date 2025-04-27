<?php

namespace JustB2b\Utils\Pricing;

defined('ABSPATH') || exit;

use WC_Tax;
use WC_Customer;
use Automattic\WooCommerce\Proxies\LegacyProxy;
use JustB2b\Utils\Prefixer;
use JustB2b\Models\ProductModel;
use JustB2b\Traits\LazyLoaderTrait;

class PriceCalculator
{
    use LazyLoaderTrait;

    protected ProductModel $product;
    protected ?array $taxRates = null;
    protected ?float $baseNetPrice = null;
    protected ?float $baseGrossPrice = null;
    protected ?float $finalNetPrice = null;
    protected ?float $finalGrossPrice = null;
    protected ?float $RRPNetPrice = null;
    protected ?float $RRPGrossPrice = null;
    protected ?bool $requestPrice = null;
    protected ?bool $hideProduct = null;
    protected ?bool $isPurchasable = null;

    public function __construct(ProductModel $product)
    {
        $this->initProduct($product);
    }

    protected function initProduct(ProductModel $product): void
    {
        $this->product = $product;
    }

    public function getTaxRates(): array
    {
        $this->lazyLoad($this->taxRates, [$this, 'initTaxRates']);
        return $this->taxRates;
    }

    protected function initTaxRates(): array
    {
        if (!$this->product->getWCProduct()->is_taxable()) {
            return [];
        }

        $customerId = get_current_user_id();

        if (apply_filters('woocommerce_adjust_non_base_location_prices', true)) {
            return WC_Tax::get_base_tax_rates($this->product->getWCProduct()->get_tax_class('unfiltered'));
        }

        $customer = $customerId
            ? wc_get_container()->get(LegacyProxy::class)->getInstance_of(WC_Customer::class, $customerId)
            : null;

        return WC_Tax::get_rates($this->product->getWCProduct()->get_tax_class(), $customer);
    }

    public function getRRPNetPrice(): float
    {
        $this->lazyLoad($this->RRPNetPrice, [$this, 'initRRPNetPrice']);
        return $this->RRPNetPrice;
    }

    protected function initRRPNetPrice(): float
    {
        $price = $this->calcNetFromJustB2bMeta('rrp_price');
        if ($price <= 0) {
            return $this->calcNetFromWCMeta('_price');
        }
        return $price;
    }

    public function getRRPGrossPrice(): float
    {
        $this->lazyLoad($this->RRPGrossPrice, [$this, 'initRRPGrossPrice']);
        return $this->RRPGrossPrice;
    }

    protected function initRRPGrossPrice(): float
    {
        return self::calcGrossFromNetPrice($this->getRRPNetPrice(), $this->getTaxRates());
    }

    public function getBaseNetPrice(): ?float
    {
        $this->lazyLoad($this->baseNetPrice, [$this, 'initBaseNetPrice']);
        return $this->baseNetPrice;
    }

    protected function initBaseNetPrice(): ?float
    {
        $firstRule = $this->product->getFirstFullFitRule();
        if ($firstRule) {
            $source = $firstRule->getStartPriceSource();
            if (str_starts_with($source, 'base_price') || $source === 'rrp_price') {
                return $this->calcNetFromJustB2bMeta($source);
            }
            return $this->calcNetFromWCMeta($source);
        }
        return 0;
    }

    public function getBaseGrossPrice(): float
    {
        $this->lazyLoad($this->baseGrossPrice, [$this, 'initBaseGrossPrice']);
        return $this->baseGrossPrice;
    }

    protected function initBaseGrossPrice(): float
    {
        $net = $this->getBaseNetPrice();
        return self::calcGrossFromNetPrice($net, $this->getTaxRates());
    }

    public function getFinalNetPrice(): float
    {
        $this->lazyLoad($this->finalNetPrice, [$this, 'initFinalNetPrice']);
        return $this->finalNetPrice;
    }

    protected function initFinalNetPrice(): float
    {
        $rule = $this->product->getFirstFullFitRule();
        return self::calcRule(
            $rule->getKind(),
            $rule->getValue(),
            $this->getBaseNetPrice(),
            $this->getBaseGrossPrice(),
            $this->getTaxRates()
        );
    }

    public function getFinalGrossPrice(): float
    {
        $this->lazyLoad($this->finalGrossPrice, [$this, 'initFinalGrossPrice']);
        return $this->finalGrossPrice;
    }

    protected function initFinalGrossPrice(): float
    {
        $net = $this->getFinalNetPrice();
        return self::calcGrossFromNetPrice($net, $this->getTaxRates());
    }

    public function isRequestPrice(): bool
    {
        $this->lazyLoad($this->requestPrice, [$this, 'initRequestPrice']);
        return $this->requestPrice;
    }

    protected function initRequestPrice(): bool
    {
        $rule = $this->product->getFirstFullFitRule();
        return $rule && $rule->getKind() === 'request_price';
    }

    public function isHideProduct(): bool
    {
        $this->lazyLoad($this->hideProduct, [$this, 'initHideProduct']);
        return $this->hideProduct;
    }

    protected function initHideProduct(): bool
    {
        $rule = $this->product->getFirstFullFitRule();
        return $rule && $rule->getKind() === 'hide_product';
    }

    public function isPurchasable(): bool
    {
        $this->lazyLoad($this->isPurchasable, [$this, 'initIsPurchasable']);
        return $this->isPurchasable;
    }

    protected function initIsPurchasable(): bool
    {
        $rule = $this->product->getFirstFullFitRule();
        return $rule && $rule->getKind() !== 'non_purchasable';
    }


    protected function calcNetFromWCMeta(string $key): float
    {
        $price = get_post_meta($this->product->getId(), $key, true);
        $price = self::getFloat($price);
        return wc_prices_include_tax() ? self::calcNetFromGrossPrice($price, $this->getTaxRates()) : $price;
    }

    protected function calcNetFromJustB2bMeta(string $key): float
    {
        $price = carbon_get_post_meta($this->product->getId(), Prefixer::getPrefixed($key));
        $price = self::getFloat($price);
        $isNet = get_option(Prefixer::getPrefixedMeta($key)) !== 'gross';
        return $isNet ? $price : self::calcNetFromGrossPrice($price, $this->getTaxRates());
    }

    public static function getFloat($value): float
    {
        $string = (string) $value;
        $normalized = str_replace(',', '.', trim($string));
        return abs((float) $normalized);
    }

    public static function calcNetFromGrossPrice(float $gross, $taxRates): float
    {
        $removeTaxes = WC_Tax::calc_tax($gross, $taxRates, true);
        return $gross - array_sum($removeTaxes);
    }

    public static function calcGrossFromNetPrice(float $net, $taxRates): float
    {
        $addTaxes = WC_Tax::calc_tax($net, $taxRates, false);
        return $net + array_sum($addTaxes);
    }

    public static function calcRule($kind, $value, $baseNet, $baseGross, $taxRates): float
    {
        switch ($kind) {
            case 'start_price':
                return $baseNet;
            case 'net_minus_percent':
                return max(0, $baseNet - $baseNet * $value * 0.01);
            case 'gross_minus_percent':
                return self::calcNetFromGrossPrice($baseGross - $baseGross * $value * 0.01, $taxRates);
            case 'net_plus_percent':
                return $baseNet + $baseNet * $value * 0.01;
            case 'gross_plus_percent':
                return self::calcNetFromGrossPrice($baseGross + $baseGross * $value * 0.01, $taxRates);
            case 'net_minus_number':
                return max(0, $baseNet - $value);
            case 'gross_minus_number':
                return self::calcNetFromGrossPrice(max(0, $baseGross - $value), $taxRates);
            case 'net_plus_number':
                return $baseNet + $value;
            case 'gross_plus_number':
                return self::calcNetFromGrossPrice($baseGross + $value, $taxRates);
            case 'net_equals_number':
                return $value;
            case 'gross_equals_number':
                return self::calcNetFromGrossPrice($value, $taxRates);
            default:
                return 0;
        }
    }
}
