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
    protected ?string $defaultPriceHtml = null;
    protected ?bool $isInLoop = null;
    protected ?string $baseNetPrice = null;
    protected ?string $baseGrossPrice = null;
    protected ?string $finalNetPrice = null;
    protected ?string $finalGrossPrice = null;
    protected ?string $rrpNetPrice = null;
    protected ?string $rrpGrossPrice = null;

    public function __construct(
        ProductModel $product,
        string $defaultPriceHtml,
        bool $isInLoop
    ) {
        $this->product = $product;
        $this->defaultPriceHtml = $defaultPriceHtml;
        $this->isInLoop = $isInLoop;
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

    protected function showPriceByKey(string $key): bool
    {
        $userController = UsersController::getInstance();
        $currentUser = $userController->getCurrentUser();

        $prefix = $currentUser->isB2b() ? 'b2b' : 'b2c';
        $optionKey = Prefixer::getPrefixedMeta("{$prefix}_{$key}");
        $value = get_option($optionKey, 'show');

        if ($value === 'show') {
            return true;
        }

        return ($this->isInLoop && $value === 'only_loop') || (!$this->isInLoop && $value === 'only_product');
    }

    public function getPriceItem($key, $price)
    {
        $html = '';
        if (!empty($price) && $this->showPriceByKey($key)) {
            $prefix = $this->getPriceTail($key, true);
            $postfix = $this->getPriceTail($key, false);
            $class = 'justb2b-price justb2b-price-' . str_replace('_', '-', $key);
            $html .= "<div class=\"{$class}\">
                {$prefix}
                <div class=\"justb2b-price-value\">{$price}</div>
                {$postfix}
            </div>";
        }
        return $html;
    }

    public function getPriceTail($key, $isPrefix)
    {
        $position = $isPrefix ? 'prefix' : 'postfix';
        $place = $this->isInLoop ? 'loop' : 'single';
        $value = get_option(Prefixer::getPrefixedMeta("{$position}_{$key}_{$place}"));
        return empty($value) ? '' : "<div class=\"justb2b-{$position}\">$value</div>";
    }

    public function renderPricesHtml()
    {
        if ($this->product->isSimpleProduct()) {
            $rule = $this->product->getFirstFullFitRule();
            if ($rule) {
                $html = '';
                $html .= $this->getPriceItem('base_net', $this->getBaseNetPrice());
                $html .= $this->getPriceItem('base_gross', $this->getBaseGrossPrice());
                $html .= $this->getPriceItem('final_net', $this->getFinalNetPrice());
                $html .= $this->getPriceItem('final_gross', $this->getFinalGrossPrice());
                $html .= $this->getPriceItem('rrp_net', $this->getRRPNetPrice());
                $html .= $this->getPriceItem('rrp_gross', $this->getRRPGrossPrice());
                $html .= $this->getCustomHtml1();

                if ($rule && current_user_can('administrator')) {
                    $html .= "rule title " . $rule->getTitle();
                }
                return $this->handlePricesHtmlContainer($html);
            }
        }
        return $this->handlePricesHtmlContainer($this->defaultPriceHtml);
    }

    public function handlePricesHtmlContainer(string $pricesHtml): string
    {

        if (
            $this->isInLoop
            || !$this->product->isSimpleProduct()
            || $this->product->isSimpleProduct() && defined('DOING_AJAX')
        ) {
            return $pricesHtml;
        }

        $productId = $this->product->getID();
        $qtyTable = $this->getQtyTable();
        $b2cHtml = $this->getHtml();

        return <<<HTML
            <div class="justb2b_product" data-product_id="{$productId}">
                {$pricesHtml}
            </div>
            {$qtyTable}
            {$b2cHtml}
        HTML;
    }


    public function getQtyTable(): string
    {
        $key = 'qty_table';

        if (!$this->showPriceByKey($key)) {
            return '';
        }

        $rules = $this->getVisibleRules();
        if (empty($rules)) {
            return '';
        }

        $prefix = $this->getPriceTail($key, true);
        $postfix = $this->getPriceTail($key, false);

        return $this->renderQtyTableHtml($rules, $prefix, $postfix);
    }

    private function getVisibleRules(): array
    {
        $rules = $this->product->getRules() ?? [];
        return array_filter($rules, fn($rule) => $rule->showInQtyTable());
    }

    private function renderQtyTableHtml(array $rules, string $prefix, string $postfix): string
    {
        $rows = array_map(fn($rule) => $this->renderQtyTableRow($rule), $rules);

        return implode('', [
            '<div class="justb2b-qty-table-container">',
            $prefix,
            '<div class="justb2b-qty-table">',
            '<table>',
            '<thead>',
            '<tr>',
            '<th>' . esc_html__('Title', 'justb2b') . '</th>',
            '<th>' . esc_html__('Price source', 'justb2b') . '</th>',
            '<th>' . esc_html__('Priority', 'justb2b') . '</th>',
            '<th>' . esc_html__('Min qty', 'justb2b') . '</th>',
            '<th>' . esc_html__('Max qty', 'justb2b') . '</th>',
            '<th>' . esc_html__('Price', 'justb2b') . '</th>',
            '</tr>',
            implode('', $rows),
            '</table>',
            '</div>',
            $postfix,
            '</div>',
        ]);
    }

    private function renderQtyTableRow($rule): string
    {
        $priceCalculator = $this->product->getPriceCalculator();
        $price = PriceCalculator::calcRule(
            $rule->getKind(),
            $rule->getValue(),
            $priceCalculator->getBaseNetPrice(),
            $priceCalculator->getBaseGrossPrice(),
            $priceCalculator->getTaxRates()
        );

        return implode('', [
            '<tr>',
            '<td>' . esc_html($rule->getTitle()) . '</td>',
            '<td>' . esc_html($rule->getPrimaryPriceSource()) . '</td>',
            '<td>' . esc_html($rule->getPriority()) . '</td>',
            '<td>' . esc_html($rule->getMinQty()) . '</td>',
            '<td>' . esc_html($rule->getMaxQty()) . '</td>',
            '<td>' . wc_price($price) . '</td>',
            '</tr>',
        ]);
    }


    private function getFormattedHtml(?string $html, string $wrapperClass): string
    {
        if (!empty(trim($html ?? ''))) {
            return '<div class="' . esc_attr($wrapperClass) . '">' . apply_filters('the_content', $html) . '</div>';
        }
        return '';
    }

    public function getHtml(): string
    {
        if (!$this->isInLoop) {
            $userType = UsersController::getInstance()->getCurrentUser()->isB2b() ? 'b2b' : 'b2c';
            $showHtml = get_option(Prefixer::getPrefixedMeta("show_{$userType}_html_1")) !== 'hide';
            if ($showHtml) {
                $html = get_option(Prefixer::getPrefixedMeta("{$userType}_html_1"));
                return $this->getFormattedHtml($html, "justb2b-{$userType}-html");
            }
        }
        return '';
    }

    private function getCustomHtml1(): string
    {
        $rule = $this->product->getFirstFullFitRule();
        if ($rule) {
            $ruleHtml1 = carbon_get_post_meta($rule->getId(), Prefixer::getPrefixed('custom_html_1'));
            return $this->getFormattedHtml($ruleHtml1, 'justb2b-rule-html-1');
        }
        return '';
    }

}
