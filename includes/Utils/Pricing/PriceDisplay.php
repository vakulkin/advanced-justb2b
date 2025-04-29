<?php

namespace JustB2b\Utils\Pricing;

use JustB2b\Controllers\UsersController;
use JustB2b\Models\ProductModel;
use JustB2b\Utils\Prefixer;
use JustB2b\Traits\LazyLoaderTrait;

defined('ABSPATH') || exit;

class PriceDisplay
{
    use LazyLoaderTrait;

    protected ProductModel $product;

    protected ?string $baseNetPrice = null;
    protected ?string $baseGrossPrice = null;
    protected ?string $finalNetPrice = null;
    protected ?string $finalGrossPrice = null;
    protected ?string $rrpNetPrice = null;
    protected ?string $rrpGrossPrice = null;

    public function __construct(ProductModel $product)
    {
        $this->product = $product;
    }

    public function getBaseNetPrice(): string
    {
        $this->lazyLoad($this->baseNetPrice, [$this, 'initBaseNetPrice']);
        return $this->baseNetPrice;
    }

    protected function initBaseNetPrice(): string
    {
        $productCalculator = $this->product->getPriceCalculator();
        $baseNetPrice = $productCalculator->getBaseNetPrice();
        $finalNetPrice = $productCalculator->getFinalNetPrice();

        if (empty($baseNetPrice) || $finalNetPrice >= $baseNetPrice) {
            return '';
        }

        return wc_price($baseNetPrice);
    }

    public function getBaseGrossPrice(): string
    {
        $this->lazyLoad($this->baseGrossPrice, [$this, 'initBaseGrossPrice']);
        return $this->baseGrossPrice;
    }

    protected function initBaseGrossPrice(): string
    {
        $productCalculator = $this->product->getPriceCalculator();
        $baseGrossPrice = $productCalculator->getBaseGrossPrice();
        $finalGrossPrice = $productCalculator->getFinalGrossPrice();

        if (empty($baseGrossPrice) || $finalGrossPrice >= $baseGrossPrice) {
            return '';
        }

        return wc_price($baseGrossPrice);
    }

    public function getFinalNetPrice(): string
    {
        $this->lazyLoad($this->finalNetPrice, [$this, 'initFinalNetPrice']);
        return $this->finalNetPrice;
    }

    protected function initFinalNetPrice(): string
    {
        $productCalculator = $this->product->getPriceCalculator();
        $finalNetPrice = $productCalculator->getFinalNetPrice();

        if (empty($finalNetPrice)) {
            return '';
        }

        return wc_price($finalNetPrice);
    }

    public function getFinalGrossPrice(): string
    {
        $this->lazyLoad($this->finalGrossPrice, [$this, 'initFinalGrossPrice']);
        return $this->finalGrossPrice;
    }

    protected function initFinalGrossPrice(): string
    {
        $productCalculator = $this->product->getPriceCalculator();
        $finalGrossPrice = $productCalculator->getFinalGrossPrice();

        if (empty($finalGrossPrice)) {
            return '';
        }

        return wc_price($finalGrossPrice);
    }

    public function getRRPNetPrice(): string
    {
        $this->lazyLoad($this->rrpNetPrice, [$this, 'initRRPNetPrice']);
        return $this->rrpNetPrice;
    }

    protected function initRRPNetPrice(): string
    {
        $productCalculator = $this->product->getPriceCalculator();
        $rrpNetPrice = $productCalculator->getRRPNetPrice();

        if (empty($rrpNetPrice)) {
            return '';
        }

        return wc_price($rrpNetPrice);
    }

    public function getRRPGrossPrice(): string
    {
        $this->lazyLoad($this->rrpGrossPrice, [$this, 'initRRPGrossPrice']);
        return $this->rrpGrossPrice;
    }

    protected function initRRPGrossPrice(): string
    {
        $productCalculator = $this->product->getPriceCalculator();
        $rrpGrossPrice = $productCalculator->getRRPGrossPrice();

        if (empty($rrpGrossPrice)) {
            return '';
        }

        return wc_price($rrpGrossPrice);
    }

    protected function showPriceByKey(string $key, bool $isLoop = false): bool
    {
        $userController = UsersController::getInstance();
        $currentUser = $userController->getCurrentUser();

        $prefix = $currentUser->isB2b() ? 'b2b' : 'b2c';
        $optionKey = Prefixer::getPrefixedMeta("{$prefix}_{$key}");
        $value = get_option($optionKey, 'show');

        if ($value === 'show') {
            return true;
        }

        return ($isLoop && $value === 'only_loop') || (!$isLoop && $value === 'only_product');
    }

    public function getPriceItem($key, $price, $isLoop)
    {
        $html = '';
        if (!empty($price) && $this->showPriceByKey($key, $isLoop)) {
            $prefix = $this->getPriceTail($key, true, $isLoop);
            $postfix = $this->getPriceTail($key, false, $isLoop);
            $class = 'justb2b-price justb2b-price-' . str_replace('_', '-', $key);
            $html .= "<div class=\"{$class}\">
                {$prefix}
                <div class=\"justb2b-price-value\">{$price}</div>
                {$postfix}
            </div>";
        }
        return $html;
    }

    public function getPriceTail($key, $isPrefix, $isLoop)
    {
        $position = $isPrefix ? 'prefix' : 'postfix';
        $place = $isLoop ? 'loop' : 'single';
        $value = get_option(Prefixer::getPrefixedMeta("{$position}_{$key}_{$place}"));
        return empty($value) ? '' : "<div class=\"justb2b-{$position}\">$value</div>";
    }

    public function getPrices($isLoop = false)
    {
        $html = '';
        $html .= $this->getPriceItem('base_net', $this->getBaseNetPrice(), $isLoop);
        $html .= $this->getPriceItem('base_gross', $this->getBaseGrossPrice(), $isLoop);
        $html .= $this->getPriceItem('final_net', $this->getFinalNetPrice(), $isLoop);
        $html .= $this->getPriceItem('final_gross', $this->getFinalGrossPrice(), $isLoop);
        $html .= $this->getPriceItem('rrp_net', $this->getRRPNetPrice(), $isLoop);
        $html .= $this->getPriceItem('rrp_gross', $this->getRRPGrossPrice(), $isLoop);
        return $html;
    }

    public function getQtyTable($isLoop = false)
    {
        $html = '';
        $key = 'qty_table';

        if ($this->showPriceByKey($key, $isLoop)) {
            $prefix = $this->getPriceTail($key, true, $isLoop);
            $postfix = $this->getPriceTail($key, false, $isLoop);

            $rules = $this->product->getRules();
            $priceCalculator = $this->product->getPriceCalculator();
            if (!empty($rules)) {
                $html .= '<div class="justb2b-qty-table-container">';
                $html .= $prefix;
                $html .= '<div class="justb2b-qty-table">';
                $html .= '<table>';
                foreach ($rules as $rule) {
                    if (!$rule->showInQtyTable()) {
                        continue;
                    }
                    $price = PriceCalculator::calcRule(
                        $rule->getKind(),
                        $rule->getValue(),
                        $priceCalculator->getBaseNetPrice(),
                        $priceCalculator->getBaseGrossPrice(),
                        $priceCalculator->getTaxRates(),
                    );
                    $formattedPrice = wc_price($price);

                    $html .= '<tr>';
                    $html .= '<td>' . $rule->getTitle() . '</td>';
                    $html .= '<td>' . $rule->getPrimaryPriceSource() . '</td>';
                    $html .= '<td>' . $rule->getPriority() . '</td>';
                    $html .= '<td>' . $rule->getMinQty() . '</td>';
                    $html .= '<td>' . $rule->getMaxQty() . '</td>';
                    $html .= '<td>' . $formattedPrice . '</td>';
                    $html .= '<tr>';
                }
                $html .= '</table>';
                $html .= '</div>';
                $html .= $postfix;
                $html .= '</div>';
            }
        }
        return $html;
    }

    public function getB2cHtml($isLoop = false)
    {
        if (!$isLoop) {
            $showB2cHtml = get_option(Prefixer::getPrefixedMeta('show_b2c_html')) !== 'hide';
            if ($showB2cHtml) {
                $b2cHtml = get_option(Prefixer::getPrefixedMeta('b2c_html'));
                if (!empty(trim($b2cHtml))) {
                    return '<div class="justb2b-b2c-html">' . apply_filters('the_content', $b2cHtml) . '</div>';
                }
            }
        }
        return '';
    }
}
