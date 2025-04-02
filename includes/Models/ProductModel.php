<?php

namespace JustB2b\Models;


defined('ABSPATH') || exit;

use WP_Query;
use JustB2b\Utils\Prefixer;
use JustB2b\Utils\Pricing\PriceCalculator;

class ProductModel extends BaseModel
{
    protected array $rules;
    protected float $startPrice;
    protected PriceCalculator $priceCalculator;
    protected static string $key = 'product';

    public function __construct(int $id, int $conditionUserId = null, int $conditionQty = null)
    {
        parent::__construct($id);
        $this->initRules($conditionUserId, $conditionQty);
        $this->initStartPrice();
        $this->initPriceCalculator();
    }

    public function getFinalPrice()
    {
        return $this->priceCalculator->getFinalPrice();
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
            'posts_per_page' => 1,
            'orderby' => 'meta_value_num',
            'meta_key' => Prefixer::getPrefixedMeta('priority'),
            'order' => 'ASC',
        ];

        if ($conditionQty !== null) {
            $args['meta_query'] = [
                [
                    'key' => Prefixer::getPrefixedMeta('min_qty'),
                    'value' => $conditionQty,
                    'compare' => '<=',
                    'type' => 'NUMERIC',
                ],
                [
                    'key' => Prefixer::getPrefixedMeta('max_qty'),
                    'value' => $conditionQty,
                    'compare' => '>=',
                    'type' => 'NUMERIC',
                ],
            ];
        }

        return $args;
    }

    protected function initPriceCalculator(): void
    {
        $rule = $this->getFirstRule();
        if ($rule !== null) {
            $this->priceCalculator = new PriceCalculator($this->startPrice, $rule->getKind(), $rule->getValue());
        }
    }

    protected function initStartPrice()
    {
        $firstRule = $this->getFirstRule();
        $startPriceSource = $firstRule->getStartPriceSource();
        switch ($startPriceSource) {
            case 'regular_price':
                $this->startPrice = (float) get_post_meta($this->id, '_regular_price', true);
                return;
            case 'sale_price':
                $this->startPrice = (float) get_post_meta($this->id, '_sale_price', true);
                return;
            case 'base_price_1':
                $this->startPrice = (float) carbon_get_post_meta($this->id, Prefixer::getPrefixed('base_price_1'));
                return;
            case 'base_price_2':
                $this->startPrice = (float) carbon_get_post_meta($this->id, Prefixer::getPrefixed('base_price_2'));
                return;
            case 'base_price_3':
                $this->startPrice = (float) carbon_get_post_meta($this->id, Prefixer::getPrefixed('base_price_3'));
                return;
            case 'base_price_4':
                $this->startPrice = (float) carbon_get_post_meta($this->id, Prefixer::getPrefixed('base_price_4'));
                return;
            case 'base_price_5':
                $this->startPrice = (float) carbon_get_post_meta($this->id, Prefixer::getPrefixed('base_price_5'));
                return;
        }
        $this->startPrice = (float) get_post_meta($this->id, '_price', true);
    }

}