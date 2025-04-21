<?php

namespace JustB2b\Models;

use JustB2b\Utils\Pricing\PriceDisplay;


defined('ABSPATH') || exit;

use WP_Query;
use WC_Product;

use JustB2b\Utils\Prefixer;
use JustB2b\Utils\Pricing\PriceCalculator;

class ProductModel extends BasePostModel
{
    protected WC_Product $WCProduct;
    protected array $rules;
    protected float $startNetPrice;
    protected PriceCalculator $priceCalculator;
    protected PriceDisplay $priceDisplay;
    protected static string $key = 'product';

    public function __construct(int $id, int $conditionUserId = null, int $conditionQty = null)
    {
        parent::__construct($id);
        $this->WCProduct = wc_get_product($id);

        $this->initRules($conditionUserId, $conditionQty);
        $this->initPriceCalculator();
        $this->initPriceDisplay();
    }

    public function getWCProduct(): WC_Product
    {
        return $this->WCProduct;
    }

    public function getFinalNetPrice()
    {
        return $this->priceCalculator->getFinalNetPrice();
    }

    public function getRules(): array
    {
        return $this->rules;
    }

    public function getFirstRule(): ?RuleModel
    {
        return $this->rules[0] ?? null;
    }

    public function initRules(int $conditionUserId = null, int $conditionQty = null): void
    {
        $query = new WP_Query(self::getRuleQueryArgs($conditionQty));
        $results = [];
        foreach ($query->posts as $post) {
            $rule = new RuleModel($post->ID, $this->id, $conditionUserId);
            if ($rule->isFits()) {
                $results[] = $rule;
            }
        }
        $this->rules = $results;
    }

    protected function getRuleQueryArgs(int $conditionQty = null): array
    {
        $args = [
            'post_type' => Prefixer::getPrefixed('rule'),
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'meta_query' => [
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
            ],
            'orderby' => [
                'priority_clause' => 'ASC',
                'min_qty_clause' => 'ASC',
                'max_qty_clause' => 'ASC',
                'ID' => 'ASC',
            ],
        ];

        if ($conditionQty !== null) {
            $args['meta_query']['min_qty_clause']['value'] = $conditionQty;
            $args['meta_query']['min_qty_clause']['compare'] = '<=';

            $args['meta_query']['max_qty_clause']['value'] = $conditionQty;
            $args['meta_query']['max_qty_clause']['compare'] = '>=';
        }

        return $args;
    }

    public function getPriceCalculator(): PriceCalculator
    {
        return $this->priceCalculator;
    }

    protected function initPriceCalculator(): void
    {
        $this->priceCalculator = new PriceCalculator($this);

    }

    public function getPriceDisplay(): PriceDisplay
    {
        return $this->priceDisplay;
    }

    protected function initPriceDisplay()
    {
        $this->priceDisplay = new PriceDisplay($this);
    }

}