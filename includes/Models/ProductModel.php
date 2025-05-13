<?php

namespace JustB2b\Models;

use WP_Query;
use WC_Product;
use JustB2b\Controllers\UsersController;
use JustB2b\Utils\Prefixer;
use JustB2b\Utils\Pricing\PriceCalculator;
use JustB2b\Utils\Pricing\PriceDisplay;
use JustB2b\Fields\NonNegativeFloatField;
use JustB2b\Traits\RuntimeCacheTrait;

defined('ABSPATH') || exit;

class ProductModel extends AbstractPostModel
{
    use RuntimeCacheTrait;

    protected static string $key = 'product';
    protected int $qty;

    public function __construct(int $id, int $conditionQty)
    {
        parent::__construct($id);
        $this->qty = $conditionQty;
    }

    public static function getSingleName(): string
    {
        return __('Product', 'justb2b');
    }

    public static function getPluralName(): string
    {
        return __('Products', 'justb2b');
    }

    public function getQty(): int
    {
        return $this->qty;
    }

    public function getWCProduct(): WC_Product
    {
        return $this->getFromRuntimeCache(
            "wc_product_{$this->id}",
            fn() => wc_get_product($this->id)
        );
    }

    public function isSimpleProduct(): bool
    {
        return $this->getFromRuntimeCache(
            "is_simple_{$this->id}",
            fn() => $this->getWCProduct()->is_type('simple')
        );
    }

    public function isVariableProduct(): bool
    {
        return $this->getFromRuntimeCache(
            "is_variable_{$this->id}",
            fn() => $this->getWCProduct()->is_type('variable')
        );
    }

    public function isVariation(): bool
    {
        return $this->getFromRuntimeCache(
            "is_variation_{$this->id}",
            fn() => $this->getWCProduct()->is_type('variation')
        );
    }

    public function isDifferentTypeProduct(): bool
    {
        return $this->getFromRuntimeCache(
            "is_other_type_{$this->id}",
            fn() => !$this->isSimpleProduct() && !$this->isVariableProduct() && !$this->isVariation()
        );
    }

    public function getRules(): array
    {
        return $this->getFromRuntimeCache("product_rules_{$this->id}_qty_{$this->qty}", function () {
            $query = new WP_Query($this->getRuleQueryArgs());
            $results = [];

            foreach ($query->posts as $post) {
                $rule = new RuleModel($post->ID, $this);
                if ($rule->isAssociationFit()) {
                    $results[] = $rule;
                }
            }

            return $results;
        });
    }

    public function getFirstFullFitRule(): ?RuleModel
    {
        return $this->getFromRuntimeCache(
            "product_rule_fit_{$this->id}_qty_{$this->qty}",
            function () {
                foreach ($this->getRules() as $rule) {
                    if ($rule->doesQtyFits()) {
                        return $rule;
                    }
                }
                return null;
            }
        );
    }

    public function getPriceCalculator(): PriceCalculator
    {
        return $this->getFromRuntimeCache(
            "product_calc_{$this->id}_qty_{$this->qty}",
            fn() => new PriceCalculator($this)
        );
    }

    public function getPriceDisplay(string $defaultPriceHtml, bool $isInLoop): PriceDisplay
    {
        return $this->getFromRuntimeCache(
            "product_display_{$this->id}_{$isInLoop}",
            fn() => new PriceDisplay($this, $defaultPriceHtml, $isInLoop)
        );
    }

    protected function getRuleQueryArgs(): array
    {
        $user = UsersController::getInstance()->getCurrentUser();

        return [
            'post_type' => Prefixer::getPrefixed('rule'),
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'meta_query' => [
                'relation' => 'AND',
                'priority_clause' => [
                    'key' => Prefixer::getPrefixedMeta('priority'),
                    'type' => 'NUMERIC',
                ],
                'min_qty_clause' => [
                    'key' => Prefixer::getPrefixedMeta('min_qty'),
                    'type' => 'NUMERIC',
                ],
                'max_qty_clause' => [
                    'key' => Prefixer::getPrefixedMeta('max_qty'),
                    'type' => 'NUMERIC',
                ],
                'user_type_clause' => [
                    'relation' => 'OR',
                    [
                        'key' => Prefixer::getPrefixedMeta('user_type'),
                        'value' => $user->isB2b() ? ['b2b', 'b2x'] : ['b2c', 'b2x'],
                        'compare' => 'IN',
                    ],
                    [
                        'key' => Prefixer::getPrefixedMeta('user_type'),
                        'compare' => 'NOT EXISTS',
                    ],
                ]
            ],
            'orderby' => [
                'priority_clause' => 'ASC',
                'min_qty_clause' => 'ASC',
                'max_qty_clause' => 'DESC',
                'ID' => 'ASC',
            ],
        ];
    }

    public static function getFieldsDefinition(): array
    {
        return [
            new NonNegativeFloatField('rrp_price', 'rrp_price'),
            new NonNegativeFloatField('base_price_1', 'base_price_1'),
            new NonNegativeFloatField('base_price_2', 'base_price_2'),
            new NonNegativeFloatField('base_price_3', 'base_price_3'),
            new NonNegativeFloatField('base_price_4', 'base_price_4'),
            new NonNegativeFloatField('base_price_5', 'base_price_5'),
        ];
    }
}