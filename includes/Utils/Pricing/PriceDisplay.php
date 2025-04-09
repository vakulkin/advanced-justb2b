<?php

namespace JustB2b\Utils\Pricing;

use JustB2b\Models\ProductModel;
use JustB2b\Utils\Prefixer;


defined('ABSPATH') || exit;

class PriceDisplay
{
    protected ProductModel $product;

    public function __construct(ProductModel $product)
    {
        $this->product = $product;
    }

    public function getPrices()
    {


    }

    public function getQtyTable()
    {
        $html = '';
        $rules = $this->product->getRules();
        $priceCalcualtor = $this->product->getPriceCalculator();
        if (!empty($rules)) {
            $html .= '<table>';
            foreach ($rules as $rule) {
                // if ($rule->showInTable()) {
                //     continue;
                // }

                $price = PriceCalculator::calcRule(
                    $rule->getKind(),
                    $rule->getValue(),
                    $priceCalcualtor->getBaseNetPrice(),
                    $priceCalcualtor->getBaseGrossPrice(),
                    $priceCalcualtor->getTaxRates(),
                );
                
                $html .= '<tr>';
                $html .= '<td>|' . $rule->getTitle() . '</td>';
                $html .= '<td>|' . $rule->getStartPriceSource() . '</td>';
                $html .= '<td>|' . $rule->getPriority() . '</td>';
                $html .= '<td>|' . $rule->getMinQty() . '</td>';
                $html .= '<td>|' . $rule->getMaxQty() . '</td>';
                $html .= '<td>|' . $price . '</td>';
                $html .= '<tr>';
            }
            $html .= '</table>';
        }
        return $html;
    }

    public function getB2BPrices()
    {
        $html = '';
        $showB2BBaseNet = get_option('b2b_base_net') !== 'hide';
        if ($showB2BBaseNet) {
            $html .= '<div class="justb2b-price justb2b-price-b2b-base-net">
                <span class="justb2b-price-label">' . __('B2B Base Net Price', Prefixer::getTextdomain()) . '</span>
                <span class="justb2b-price-value">' . $this->getBaseNetPrice() . '</span>
            </div>';
        }
        $html .= $this->getQtyTable();
        return $html;
    }

    public function getBaseNetPrice(): string
    {
        $productCalculator = $this->product->getPriceCalculator();
        $baseNetPrice = $productCalculator->getBaseNetPrice();
        if ($baseNetPrice === null) {
            return '';
        }
        return wc_price($baseNetPrice);
    }

    public function getBaseGrossPrice(): string
    {
        $productCalculator = $this->product->getPriceCalculator();
        $baseGrossPrice = $productCalculator->getBaseGrossPrice();
        if ($baseGrossPrice === null) {
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
}