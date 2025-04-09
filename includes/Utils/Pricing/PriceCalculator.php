<?php

namespace JustB2b\Utils\Pricing;


defined('ABSPATH') || exit;

use WC_Tax;
use WC_Customer;
use Automattic\WooCommerce\Proxies\LegacyProxy;

use JustB2b\Utils\Prefixer;
use JustB2b\Models\ProductModel;

class PriceCalculator
{
    protected ProductModel $product;
    protected array $taxRates = [];
    protected ?float $baseNetPrice = null;
    protected ?float $baseGrossPrice = null;
    protected ?float $finalNetPrice = null;
    protected ?float $finalGrossPrice = null;
    protected ?float $RRPNetPrice = null;
    protected bool $requestPrice = false;
    protected bool $hideProduct = false;


    public function __construct(ProductModel $product)
    {
        $this->product = $product;

        $rule = $this->product->getFirstRule();
        if ($rule === null) {
            return;
        }

        $this->initTaxRates();

        $this->initRRPNetPrice();

        $this->initBaseNetPrice();
        $this->initBaseGrossPrice();

        $this->initFinalNetPrice();
        $this->initFinalGrossPrice();

        $this->initRequestPrice();
        $this->initHideProduct();
    }

    public function getTaxRates(): array
    {
        return $this->taxRates;
    }

    public function initTaxRates(): void
    {
        if ($this->product->getWCProduct()->is_taxable()) {
            $customer_id = get_current_user_id();
            if (apply_filters('woocommerce_adjust_non_base_location_prices', true)) {
                $this->taxRates = WC_Tax::get_base_tax_rates($this->product->getWCProduct()->get_tax_class('unfiltered'));
                return;
            }
            $customer = $customer_id ? wc_get_container()->get(LegacyProxy::class)->get_instance_of(WC_Customer::class, $customer_id) : null;
            $this->taxRates = WC_Tax::get_rates($this->product->getWCProduct()->get_tax_class(), $customer);
            return;
        }
        $this->taxRates = [];
        return;
    }

    public function getRRPNetPrice(): ?float
    {
        return $this->RRPNetPrice;
    }

    public function initRRPNetPrice(): void
    {
        $justB2bRRPNetPrice = $this->calcNetFromJustB2bMeta('rrp_price');
        if ($justB2bRRPNetPrice <= 0) {
            $WCPrice = $this->calcNetFromWCMeta('_price');
            if ($WCPrice) {
                $this->RRPNetPrice = $WCPrice;
            }
            return;
        }
        $this->RRPNetPrice = $justB2bRRPNetPrice;
    }

    public function getBaseNetPrice(): ?float
    {
        return $this->baseNetPrice;
    }

    protected function initBaseNetPrice(): void
    {
        $firstRule = $this->product->getFirstRule();

        $startPriceSource = $firstRule->getStartPriceSource();
        switch ($startPriceSource) {
            case 'rrp_price':
            case 'base_price_1':
            case 'base_price_2':
            case 'base_price_3':
            case 'base_price_4':
            case 'base_price_5':
                $this->baseNetPrice = $this->calcNetFromJustB2bMeta($startPriceSource);
                break;
            default:
                $this->baseNetPrice = $this->calcNetFromWCMeta($startPriceSource);
                break;
        }
    }

    public function getBaseGrossPrice(): ?float
    {
        return $this->baseGrossPrice;
    }

    protected function initBaseGrossPrice(): void
    {
        $this->baseGrossPrice = self::calcGrossFromNetPrice($this->baseNetPrice, $this->taxRates);
    }

    public function getFinalNetPrice(): ?float
    {
        return $this->finalNetPrice;
    }

    public static function calcRule($kind, $value, $baseNetPrice, $baseGrossPrice, $taxRates): ?float
    {
        switch ($kind) {
            case 'start_price':
                return $baseNetPrice;
            case 'net_minus_percent':
                return max(0, $baseNetPrice - $baseNetPrice * $value * 0.01);
            case 'gross_minus_percent':
                return self::calcNetFromGrossPrice($baseGrossPrice - $baseGrossPrice * $value * 0.01, $taxRates);
            case 'net_plus_percent':
                return $baseNetPrice + $baseNetPrice * $value * 0.01;
            case 'gross_plus_percent':
                return self::calcNetFromGrossPrice($baseGrossPrice + $baseGrossPrice * $value * 0.01, $taxRates);
            case 'net_minus_number':
                return max(0, $baseNetPrice - $value);
            case 'gross_minus_number':
                return self::calcNetFromGrossPrice(max(0, $baseGrossPrice - $value), $taxRates);
            case 'net_plus_number':
                return $baseNetPrice + $value;
            case 'gross_plus_number':
                return self::calcNetFromGrossPrice($baseGrossPrice + $value, $taxRates);
            case 'net_equals_number':
                return $value;
            case 'gross_equals_number':
                return self::calcNetFromGrossPrice($value, $taxRates);
        }
        return null;
    }

    protected function initFinalNetPrice(): void
    {
        $rule = $this->product->getFirstRule();

        $this->finalNetPrice = self::calcRule(
            $rule->getKind(),
            $rule->getValue(),
            $this->baseNetPrice,
            $this->baseGrossPrice,
            $this->taxRates
        );
    }

    public function getFinalGrossPrice(): ?float
    {
        return $this->finalGrossPrice;
    }

    protected function initFinalGrossPrice(): void
    {
        if ($this->finalNetPrice) {
            $this->finalGrossPrice = self::calcGrossFromNetPrice($this->finalNetPrice, $this->taxRates);
        }
    }

    protected function initRequestPrice(): void
    {
        $rule = $this->product->getFirstRule();
        $this->requestPrice = $rule->getKind() === 'request_price';
    }

    protected function initHideProduct(): void
    {
        $rule = $this->product->getFirstRule();
        $this->hideProduct = $rule->getKind() === 'hide_product';
    }


    protected function calcNetFromWCMeta(string $key): float
    {
        $WCPrice = get_post_meta($this->product->getId(), $key, true);
        $WCPrice = PriceCalculator::getFloat($WCPrice);
        return wc_prices_include_tax() ? PriceCalculator::calcNetFromGrossPrice($WCPrice, $this->taxRates) : $WCPrice;
    }

    protected function calcNetFromJustB2bMeta(string $key): float
    {
        $RRPPrice = carbon_get_post_meta($this->product->getId(), Prefixer::getPrefixed($key));
        $RRPPrice = PriceCalculator::getFloat($RRPPrice);
        $isRRPPriceIsNet = carbon_get_theme_option(Prefixer::getPrefixed($key)) !== 'gross';
        return $isRRPPriceIsNet ? $RRPPrice : PriceCalculator::calcNetFromGrossPrice($RRPPrice, $this->taxRates);
    }


    public static function getFloat($value): float
    {
        $stringValue = (string) $value;
        $normalizedValue = str_replace(',', '.', trim($stringValue));
        return abs(floatval($normalizedValue));
    }

    public static function calcNetFromGrossPrice(float $grossPrice, $taxRates): float
    {
        $removeTaxes = WC_Tax::calc_tax($grossPrice, $taxRates, true);
        return $grossPrice - array_sum($removeTaxes);
    }

    public static function calcGrossFromNetPrice(float $netPrice, $taxRates): float
    {
        $addTaxes = WC_Tax::calc_tax($netPrice, $taxRates, false);
        return $netPrice + array_sum($addTaxes);
    }
}