<?php

namespace JustB2b\Models;

use JustB2b\Utils\Prefixer;
use JustB2b\Utils\Pricing\PriceCalculator;
use JustB2b\Utils\Pricing\PriceDisplay;
use JustB2b\Traits\LazyLoaderTrait;
use WP_Query;
use WC_Product;

defined('ABSPATH') || exit;

class ProductModel extends BasePostModel
{
    use LazyLoaderTrait;

    protected static string $key = 'product';

    protected int $qty;
    protected ?WC_Product $WCProduct = null;
    protected ?array $associationsRules = null;
    protected ?RuleModel $firstFullFitRule = null;
    protected ?PriceCalculator $priceCalculator = null;
    protected ?PriceDisplay $priceDisplay = null;

    public function __construct(int $id, int $conditionQty)
    {
        parent::__construct($id);
        $this->initQty($conditionQty);
    }

    public function getWCProduct(): WC_Product
    {
        $this->initWCProduct();
        return $this->WCProduct;
    }

    protected function initWCProduct(): void
    {
        $this->lazyLoad($this->WCProduct, function () {
            return wc_get_product($this->id);
        });
    }

    public function getQty(): int
    {
        return $this->qty;
    }

    protected function initQty(int $conditionQty): void
    {
        $this->qty = $conditionQty;
    }

    public function getRules(): array
    {
        $this->initAssociationRules();
        return $this->associationsRules;
    }

    protected function initAssociationRules(): void
    {
        $this->lazyLoad($this->associationsRules, function () {
            $query = new WP_Query($this->getRuleQueryArgs());
            $results = [];

            foreach ($query->posts as $post) {
                $rule = new RuleModel($post->ID, $this->id, $this->getQty());
                if ($rule->isAssociationFits()) {
                    $results[] = $rule;
                }
            }

            return $results;
        });
    }

    public function getFirstFullFitRule(): ?RuleModel
    {
        $this->initFirstFullFitRule();
        return $this->firstFullFitRule;
    }

    protected function initFirstFullFitRule(): void
    {
        $this->lazyLoad($this->firstFullFitRule, function () {
            foreach ($this->getRules() as $rule) {
                if ($rule->isQtyFits()) {
                    return $rule;
                }
            }
            return null;
        });
    }

    public function getPriceCalculator(): PriceCalculator
    {
        $this->initPriceCalculator();
        return $this->priceCalculator;
    }

    protected function initPriceCalculator(): void
    {
        $this->lazyLoad($this->priceCalculator, function () {
            return new PriceCalculator($this);
        });
    }

    public function getPriceDisplay(): PriceDisplay
    {
        $this->initPriceDisplay();
        return $this->priceDisplay;
    }

    protected function initPriceDisplay(): void
    {
        $this->lazyLoad($this->priceDisplay, function () {
            return new PriceDisplay($this);
        });
    }

    protected function getRuleQueryArgs(): array
    {
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
            ],
            'orderby' => [
                'priority_clause' => 'ASC',
                'min_qty_clause' => 'ASC',
                'max_qty_clause' => 'DESC',
                'ID' => 'ASC',
            ],
        ];
    }
}
