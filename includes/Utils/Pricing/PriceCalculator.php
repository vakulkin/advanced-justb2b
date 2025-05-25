<?php

namespace JustB2b\Utils\Pricing;

use WC_Tax;
use WC_Customer;
use Automattic\WooCommerce\Proxies\LegacyProxy;
use JustB2b\Controllers\Key\GlobalController;
use JustB2b\Models\Id\ProductModel;
use JustB2b\Traits\RuntimeCacheTrait;

defined('ABSPATH') || exit;

/**
 * @feature-section price_rules
 * @title[ru] Гибкие правила ценообразования
 * @desc[ru] Модуль расчёта цен JustB2B поддерживает продвинутую логику: скидки и наценки на основе количества, ролей, групп, категорий, условий и приоритетов. Позволяет точно управлять B2B-ценами на уровне каждого товара.
 * @order 100
 */

class PriceCalculator
{
    use RuntimeCacheTrait;

    protected ProductModel $product;

    public function __construct(ProductModel $product)
    {
        $this->product = $product;
    }

    protected function cacheContext(array $extra = []): array
    {
        return array_merge([
            'product_id' => $this->product->getId(),
            'qty' => $this->product->getQty()
        ], $extra);
    }

    /**
     * @feature price_rules tax_rate_detection
     * @title[ru] Автоматическое определение налоговой ставки
     * @desc[ru] Учитывает налоговую ставку в зависимости от настроек магазина и местоположения покупателя.
     * @order 440
     */
    public function getTaxRates(): array
    {
        return self::getFromRuntimeCache(function () {
            $WCProduct = $this->product->getWCProduct();
            if (!$WCProduct->is_taxable()) {
                return [];
            }

            $customerId = get_current_user_id();

            if (apply_filters('woocommerce_adjust_non_base_location_prices', true)) {
                return WC_Tax::get_base_tax_rates($WCProduct->get_tax_class('unfiltered'));
            }

            $customer = $customerId
                ? wc_get_container()->get(LegacyProxy::class)->getInstance_of(WC_Customer::class, $customerId)
                : null;

            return WC_Tax::get_rates($WCProduct->get_tax_class(), $customer);
        }, $this->cacheContext());
    }

    /**
     * @feature price_rules rrp_support
     * @title[ru] Поддержка рекомендуемой розничной цены (RRP)
     * @desc[ru] Вычисляет и отображает RRP-цену с поддержкой первичного и вторичного источника: если основной источник возвращает 0, используется резервный.
     * @order 430
     */
    public function getRRPNetPrice(): float
    {
        return self::getFromRuntimeCache(function () {
            $rule = $this->product->getFirstFullFitRule();
            if ($rule) {
                $RRPNet = $this->calcNetFromJustB2bMeta('rrp_price');
                $secondaryRRPSource = $rule->getSecondaryRRPSource();
                return $this->getSecondaryPrice($RRPNet, $secondaryRRPSource);
            }
            return 0;
        }, $this->cacheContext());
    }

    public function getRRPGrossPrice(): float
    {
        return self::getFromRuntimeCache(
            fn() => self::calcGrossFromNetPrice(
                $this->getRRPNetPrice(),
                $this->getTaxRates()
            ),
            $this->cacheContext()
        );
    }

    /**
     * @feature price_rules flexible_sources
     * @title[ru] Источники цен: WC или JustB2B
     * @desc[ru] Плагин поддерживает базовые цены как из WooCommerce, так и из собственных мета-полей JustB2B.
     * @order 420
     */
    public function getBaseNetPrice(): float
    {
        return self::getFromRuntimeCache(function () {
            $rule = $this->product->getFirstFullFitRule();
            if ($rule && !$rule->isZeroRequestPrice()) {
                $primary = $this->getNetByKey($rule->getPrimaryPriceSource());
                return $this->getSecondaryPrice(
                    $primary,
                    $rule->getSecondaryPriceSource()
                );
            }
            return 0;
        }, $this->cacheContext());
    }

    public function getBaseGrossPrice(): float
    {
        return self::getFromRuntimeCache(
            fn() => self::calcGrossFromNetPrice(
                $this->getBaseNetPrice(),
                $this->getTaxRates()
            ),
            $this->cacheContext()
        );
    }

    public function getFinalNetPrice(): float
    {
        return self::getFromRuntimeCache(function () {
            return $this->calcRule();
        }, $this->cacheContext());
    }

    public function getFinalGrossPrice(): float
    {
        return self::getFromRuntimeCache(
            fn() => self::calcGrossFromNetPrice(
                $this->getFinalNetPrice(),
                $this->getTaxRates()
            ),
            $this->cacheContext()
        );
    }

    protected function getSecondaryPrice(float $primaryPrice, string $secondaryKey): float
    {
        if ('disabled' !== $secondaryKey && $primaryPrice <= 0) {
            return $this->getNetByKey($secondaryKey);
        }
        return $primaryPrice;
    }

    protected function getNetByKey(string $source): float
    {
        if (str_starts_with($source, 'base_price') || $source === 'rrp_price') {
            return $this->calcNetFromJustB2bMeta($source);
        }
        return $this->calcNetFromWCMeta($source);
    }

    protected function calcNetFromWCMeta(string $key): float
    {
        $price = get_post_meta($this->product->getId(), $key, true);
        $price = abs((float) $price);
        return wc_prices_include_tax()
            ? self::calcNetFromGrossPrice($price, $this->getTaxRates())
            : $price;
    }

    protected function calcNetFromJustB2bMeta(string $key): float
    {
        $price = $this->product->getFieldValue($key);
        $globalController = GlobalController::getInstance();
        $settingsObject = $globalController->getSettingsModelObject();
        $isNet = $settingsObject->getFieldValue($key) !== 'gross';
        return $isNet
            ? $price
            : self::calcNetFromGrossPrice($price, $this->getTaxRates());
    }

    /**
     * @feature price_rules net_gross_conversion
     * @title[ru] Конвертация между нетто и брутто
     * @desc[ru] Автоматически пересчитывает цену с учётом налога — из брутто в нетто и обратно.
     * @order 410
     */
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

    /**
     * @feature price_rules rule_engine
     * @title[ru] Многоуровневая система расчёта цен
     * @desc[ru] Определяет цену товара в зависимости от правил: процентные и числовые скидки, наценки, установка фиксированной цены, с учётом нетто и брутто.
     * @order 400
     */
    public function calcRule(): float
    {
        $rule = $this->product->getFirstFullFitRule();
        if ($rule) {
            switch ($rule->getKind()) {
                case 'price_source':
                    return $this->getBaseNetPrice();
                case 'net_minus_percent':
                    return max(0, $this->getBaseNetPrice() - $this->getBaseNetPrice() * $rule->getValue() * 0.01);
                case 'gross_minus_percent':
                    return self::calcNetFromGrossPrice($this->getBaseGrossPrice() - $this->getBaseGrossPrice() * $rule->getValue() * 0.01, $this->getTaxRates());
                case 'net_plus_percent':
                    return $this->getBaseNetPrice() + $this->getBaseNetPrice() * $rule->getValue() * 0.01;
                case 'gross_plus_percent':
                    return self::calcNetFromGrossPrice($this->getBaseGrossPrice() + $this->getBaseGrossPrice() * $rule->getValue() * 0.01, $this->getTaxRates());
                case 'net_minus_number':
                    return max(0, $this->getBaseNetPrice() - $rule->getValue());
                case 'gross_minus_number':
                    return self::calcNetFromGrossPrice(max(0, $this->getBaseGrossPrice() - $rule->getValue()), $this->getTaxRates());
                case 'net_plus_number':
                    return $this->getBaseNetPrice() + $rule->getValue();
                case 'gross_plus_number':
                    return self::calcNetFromGrossPrice($this->getBaseGrossPrice() + $rule->getValue(), $this->getTaxRates());
                case 'net_equals_number':
                    return $rule->getValue();
                case 'gross_equals_number':
                    return self::calcNetFromGrossPrice($rule->getValue(), $this->getTaxRates());
            }
        }
        return 0;
    }
}
