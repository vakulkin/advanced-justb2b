<?php

namespace JustB2b\Utils\Pricing;

use JustB2b\Controllers\Id\UsersController;
use JustB2b\Controllers\Key\GlobalController;
use JustB2b\Models\Id\ProductModel;
use JustB2b\Traits\RuntimeCacheTrait;

defined('ABSPATH') || exit;

class PriceDisplay
{
    use RuntimeCacheTrait;

    protected ProductModel $product;
    protected string $defaultPriceHtml;
    protected bool $isInLoop;

    public function __construct(ProductModel $product, string $defaultPriceHtml, bool $isInLoop)
    {
        $this->product = $product;
        $this->defaultPriceHtml = $defaultPriceHtml;
        $this->isInLoop = $isInLoop;
    }
    protected function cacheContext(array $extra = []): array
    {
        return array_merge([
            'product_id' => $this->product->getId(),
            'qty' => $this->product->getQty(),
            'is_loop' => $this->isInLoop,
        ], $extra);
    }
    public function getBaseNetPrice(): string
    {
        return self::getFromRuntimeCache(function () {
            $calc = $this->product->getPriceCalculator();
            $base = $calc->getBaseNetPrice();
            $final = $calc->getFinalNetPrice();
            return empty($base) || $final >= $base ? '' : wc_price($base);
        }, $this->cacheContext());
    }

    public function getBaseGrossPrice(): string
    {
        return self::getFromRuntimeCache(function () {
            $calc = $this->product->getPriceCalculator();
            $base = $calc->getBaseGrossPrice();
            $final = $calc->getFinalGrossPrice();
            return empty($base) || $final >= $base ? '' : wc_price($base);
        }, $this->cacheContext());
    }

    public function getFinalNetPrice(): string
    {
        return self::getFromRuntimeCache(function () {
            $price = $this->product->getPriceCalculator()->getFinalNetPrice();
            return empty($price) ? '' : wc_price($price);
        }, $this->cacheContext());
    }

    public function getFinalGrossPrice(): string
    {
        return self::getFromRuntimeCache(function () {
            $price = $this->product->getPriceCalculator()->getFinalGrossPrice();
            return empty($price) ? '' : wc_price($price);
        }, $this->cacheContext());
    }

    public function getRRPNetPrice(): string
    {
        return self::getFromRuntimeCache(function () {
            $price = $this->product->getPriceCalculator()->getRRPNetPrice();
            return empty($price) ? '' : wc_price($price);
        }, $this->cacheContext());
    }

    public function getRRPGrossPrice(): string
    {
        return self::getFromRuntimeCache(function () {
            $price = $this->product->getPriceCalculator()->getRRPGrossPrice();
            return empty($price) ? '' : wc_price($price);
        }, $this->cacheContext());
    }

    protected function showPriceByKey(string $key): bool
    {
        $userController = UsersController::getInstance();
        $currentUser = $userController->getCurrentUser();

        $prefix = $currentUser->isB2b() ? 'b2b' : 'b2c';
        $globalController = GlobalController::getInstance();
        $settingsObject = $globalController->getSettingsModelObject();
        $value = $settingsObject->getFieldValue("{$prefix}_{$key}");

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
        $currentUser = UsersController::getInstance()->getCurrentUser();
        $userKind = $currentUser->isB2b() ? 'b2b' : 'b2c';
        $position = $isPrefix ? 'prefix' : 'postfix';
        $place = $this->isInLoop ? 'loop' : 'single';
        $finalKey = "{$place}_{$userKind}_{$key}_{$position}";
        $globalController = GlobalController::getInstance();
        $settingsObject = $globalController->getSettingsModelObject();
        $value = $settingsObject->getFieldValue($finalKey);
        return empty($value) ? '' : "<div class=\"justb2b-{$position}\">$value</div>";
    }

    public function renderPricesHtml()
    {
        if ($this->product->isSimpleProduct()) {
            $rule = $this->product->getFirstFullFitRule();
            if ($rule) {
                $html = '';
                if (
                    $this->isInLoop && !$rule->isPricesInLoopHidden() ||
                    !$this->isInLoop && !$rule->isPricesInProductHidden()
                ) {
                    $html .= $this->getPriceItem('base_net', $this->getBaseNetPrice());
                    $html .= $this->getPriceItem('base_gross', $this->getBaseGrossPrice());
                    $html .= $this->getPriceItem('final_net', $this->getFinalNetPrice());
                    $html .= $this->getPriceItem('final_gross', $this->getFinalGrossPrice());
                    $html .= $this->getPriceItem('rrp_net', $this->getRRPNetPrice());
                    $html .= $this->getPriceItem('rrp_gross', $this->getRRPGrossPrice());
                }
                if (!$this->isInLoop) {
                    $html .= $this->getCustomHtml1();
                }

                if ($rule && current_user_can('administrator')) {
                    $html .= "<div class=\"justb2b-rule-title\">" . $rule->getTitle() . "</div>";
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
        $price = PriceCalculator::calcRule($rule, $priceCalculator);

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
            $globalController = GlobalController::getInstance();
            $settingsObject = $globalController->getSettingsModelObject();
            $userType = UsersController::getInstance()->getCurrentUser()->isB2b() ? 'b2b' : 'b2c';
            $showHtml = $settingsObject->getFieldValue("show_{$userType}_html_1");
            if ($showHtml) {
                $html = $settingsObject->getFieldValue("{$userType}_html_1");
                return $this->getFormattedHtml($html, "justb2b-{$userType}-html");
            }
        }
        return '';
    }

    private function getCustomHtml1(): string
    {
        $rule = $this->product->getFirstFullFitRule();
        if ($rule) {
            $ruleHtml1 = $rule->getFieldValue('custom_html_1');
            return $this->getFormattedHtml($ruleHtml1, 'justb2b-rule-html-1');
        }
        return '';
    }
}
