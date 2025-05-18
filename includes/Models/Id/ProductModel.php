<?php

namespace JustB2b\Models\Id;

use WP_Query;
use WC_Product;
use JustB2b\Controllers\Id\UsersController;
use JustB2b\Fields\NonNegativeFloatField;
use JustB2b\Traits\RuntimeCacheTrait;
use JustB2b\Utils\Prefixer;
use JustB2b\Utils\Pricing\PriceCalculator;
use JustB2b\Utils\Pricing\PriceDisplay;

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

    protected function cacheContext(array $extra = []): array
    {
        return array_merge([
            parent::cacheContext($extra),
            'qty' => $this->qty
        ]);
    }

    public function getQty(): int
    {
        return $this->qty;
    }

    public function getWCProduct(): WC_Product
    {
        return self::getFromRuntimeCache(
            fn() => wc_get_product($this->id),
            $this->cacheContext()
        );
    }

    public function isSimpleProduct(): bool
    {
        return $this->getWCProduct()->is_type('simple');
    }

    public function isVariableProduct(): bool
    {
        return $this->getWCProduct()->is_type('variable');
    }

    public function isVariation(): bool
    {
        return $this->getWCProduct()->is_type('variation');
    }

    public function isDifferentTypeProduct(): bool
    {
        return !$this->isSimpleProduct() && !$this->isVariableProduct() && !$this->isVariation();
    }

    public function getRules(): array
    {
        return self::getFromRuntimeCache(function () {
            $query = new WP_Query($this->getRuleQueryArgs());
            $results = [];

            foreach ($query->posts as $post) {
                $rule = new RuleModel($post->ID, $this->getId(), $this->getQty());
                if ($rule->isAssociationFit()) {
                    $results[] = $rule;
                }
            }

            return $results;
        }, $this->cacheContext());
    }

    public function getFirstFullFitRule(): ?RuleModel
    {
        return self::getFromRuntimeCache(function () {
            foreach ($this->getRules() as $rule) {
                if ($rule->doesQtyFits()) {
                    return $rule;
                }
            }
            return null;
        }, $this->cacheContext());
    }

    public function getPriceCalculator(): PriceCalculator
    {
        return self::getFromRuntimeCache(
            fn() => new PriceCalculator($this),
            $this->cacheContext()
        );
    }

    public function getPriceDisplay(string $defaultPriceHtml, bool $isInLoop): PriceDisplay
    {
        return self::getFromRuntimeCache(
            fn() => new PriceDisplay($this, $defaultPriceHtml, $isInLoop),
            $this->cacheContext(['is_loop' => $isInLoop])
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