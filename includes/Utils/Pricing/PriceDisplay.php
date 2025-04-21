<?php

namespace JustB2b\Utils\Pricing;

use JustB2b\Models\ProductModel;
use JustB2b\Models\UserModel;
use JustB2b\Utils\Prefixer;


defined('ABSPATH') || exit;

class PriceDisplay
{
    protected ProductModel $product;
    protected UserModel $user;

    public function __construct(ProductModel $product)
    {
        $this->product = $product;
        $this->user = new UserModel(get_current_user_id());
    }

    public function getQtyTable()
    {
        $html = '';
        $rules = $this->product->getRules();
        $priceCalcualtor = $this->product->getPriceCalculator();
        if (!empty($rules)) {
            $html .= '<table>';
            foreach ($rules as $rule) {
                if (!$rule->showInQtyTable()) {
                    continue;
                }

                $price = PriceCalculator::calcRule(
                    $rule->getKind(),
                    $rule->getValue(),
                    $priceCalcualtor->getBaseNetPrice(),
                    $priceCalcualtor->getBaseGrossPrice(),
                    $priceCalcualtor->getTaxRates(),
                );

                $html .= '<tr>';
                $html .= '<td>' . $rule->getTitle() . '</td>';
                $html .= '<td>' . $rule->getStartPriceSource() . '</td>';
                $html .= '<td>' . $rule->getPriority() . '</td>';
                $html .= '<td>' . $rule->getMinQty() . '</td>';
                $html .= '<td>' . $rule->getMaxQty() . '</td>';
                $html .= '<td>' . $price . '</td>';
                $html .= '<tr>';
            }
            $html .= '</table>';
        }
        return $html;
    }


    protected function showPriceByKey(string $key, bool $isLoop = false): bool
    {
        $prefix = $this->user->isB2b() ? 'b2b' : 'b2c';
        $optionKey = Prefixer::getPrefixedMeta("{$prefix}_{$key}");
        $value = get_option($optionKey, 'show');

        if ($value === 'show') {
            return true;
        }

        return ($isLoop && $value === 'only_loop')
            || (!$isLoop && $value === 'only_product');
    }



    public function getPrices($isLoop = false)
    {
        $html = '';

        $html .= '<div class="justb2b-price-container">';
        $basePrice = $this->getBaseNetPrice();
        if ($this->showPriceByKey('base_net', $isLoop) && !empty($basePrice)) {
            $html .= '<div class="justb2b-price justb2b-price-b2b-base-net">
            <span class="justb2b-price-label">' . __('Base Net Price', Prefixer::getTextdomain()) . '</span>
            <span class="justb2b-price-value">' . $basePrice . '</span>
            </div>';
        }

        $baseGrossPrice = $this->getBaseGrossPrice();
        if ($this->showPriceByKey('base_gross', $isLoop) && !empty($baseGrossPrice)) {
            $html .= '<div class="justb2b-price justb2b-price-b2b-base-gross">
                <span class="justb2b-price-label">' . __('Base Gross Price', Prefixer::getTextdomain()) . '</span>
                <span class="justb2b-price-value">' . $baseGrossPrice . '</span>
            </div>';
        }

        $finalNetPrice = $this->getFinalNetPrice();
        if ($this->showPriceByKey('final_net', $isLoop) && !empty($finalNetPrice)) {
            $html .= '<div class="justb2b-price justb2b-price-b2b-final-net">
                <span class="justb2b-price-label">' . __('Final Net Price', Prefixer::getTextdomain()) . '</span>
                <span class="justb2b-price-value">' . $finalNetPrice . '</span>
            </div>';
        }

        $finalGrossPrice = $this->getFinalGrossPrice();
        if ($this->showPriceByKey('final_gross', $isLoop) && !empty($finalGrossPrice)) {
            $html .= '<div class="justb2b-price justb2b-price-b2b-final-gross">
                <span class="justb2b-price-label">' . __('Final Gross Price', Prefixer::getTextdomain()) . '</span>
                <span class="justb2b-price-value">' . $finalGrossPrice . '</span>
            </div>';
        }

        $rrpNetPrice = $this->getRRPNetPrice();
        if ($this->showPriceByKey('rrp_net', $isLoop) && !empty($rrpNetPrice)) {
            $html .= '<div class="justb2b-price justb2b-price-b2b-rrp-net">
                <span class="justb2b-price-label">' . __('RRP Net Price', Prefixer::getTextdomain()) . '</span>
                <span class="justb2b-price-value">' . $rrpNetPrice . '</span>
            </div>';
        }

        $rrpGrossPrice = $this->getRRPGrossPrice();
        if ($this->showPriceByKey('rrp_gross', $isLoop) && !empty($rrpGrossPrice)) {
            $html .= '<div class="justb2b-price justb2b-price-b2b-rrp-gross">
                <span class="justb2b-price-label">' . __('RRP Gross Price', Prefixer::getTextdomain()) . '</span>
                <span class="justb2b-price-value">' . $rrpGrossPrice . '</span>
            </div>';
        }

        $html .= '</div>';

        if ($this->showPriceByKey('qty_table', $isLoop)) {
            $html .= $this->getQtyTable();
        }        

        return $html;
    }

    public function getBaseNetPrice(): string
    {
        $productCalculator = $this->product->getPriceCalculator();
        $baseNetPrice = $productCalculator->getBaseNetPrice();
        $finaNetPrice = $productCalculator->getFinalNetPrice();
        if ($baseNetPrice === null || $finaNetPrice >= $baseNetPrice) {
            return '';
        }
        return wc_price($baseNetPrice);
    }

    public function getBaseGrossPrice(): string
    {
        $productCalculator = $this->product->getPriceCalculator();
        $baseGrossPrice = $productCalculator->getBaseGrossPrice();
        $finalGrossPrice = $productCalculator->getFinalGrossPrice();
        if ($baseGrossPrice === null || $finalGrossPrice >= $baseGrossPrice) {
            return '';
        }
        return wc_price($baseGrossPrice);
    }

    public function getFinalNetPrice(): string
    {
        $productCalculator = $this->product->getPriceCalculator();
        $finalNetPrice = $productCalculator->getFinalNetPrice();
        if ($finalNetPrice === null) {
            return '';
        }
        return wc_price($finalNetPrice);
    }

    public function getFinalGrossPrice(): string
    {
        $productCalculator = $this->product->getPriceCalculator();
        $finalGrossPrice = $productCalculator->getFinalGrossPrice();
        if ($finalGrossPrice === null) {
            return '';
        }
        return wc_price($finalGrossPrice);
    }

    public function getRRPNetPrice(): string
    {
        $productCalculator = $this->product->getPriceCalculator();
        $rrpNetPrice = $productCalculator->getRRPNetPrice();
        if ($rrpNetPrice === null) {
            return '';
        }
        return wc_price($rrpNetPrice);
    }

    public function getRRPGrossPrice(): string
    {
        $productCalculator = $this->product->getPriceCalculator();
        $rrpGrossPrice = $productCalculator->getRRPGrossPrice();
        if ($rrpGrossPrice === null) {
            return '';
        }
        return wc_price($rrpGrossPrice);
    }
}