<?php

namespace JustB2b\Models;

use JustB2b\Controllers\UsersController;
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
    protected ?bool $isSimpleProduct = null;
    protected ?bool $isVariableProduct = null;
    protected ?bool $isVariation;
    protected ?bool $isDifferntTypeProduct = null;
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

    public static function getSingleName(): string {
        return __('Product', 'justb2b');
    }
    public static function getPluralName(): string {
        return __('Products', 'justb2b');
    }

    public function getWCProduct(): WC_Product
    {
        $this->initWCProduct();
        error_log($this->WCProduct->get_id());
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

    public function isSimpleProduct(): bool
    {
        $this->initIsSimpleProduct();
        return $this->isSimpleProduct;
    }

    protected function initIsSimpleProduct(): void
    {
        $this->lazyLoad($this->isSimpleProduct, function () {
            return $this->getWCProduct()->is_type('simple');
        });
    }
    public function isVariableProduct(): bool
    {
        $this->initIsVariableProduct();
        return $this->isVariableProduct;
    }
    protected function initIsVariableProduct(): void
    {
        $this->lazyLoad($this->isVariableProduct, function () {
            return $this->getWCProduct()->is_type('variable');
        });
    }
    public function isVariation(): bool
    {
        $this->initIsVariation();
        return $this->isVariation;
    }
    protected function initIsVariation(): void
    {
        $this->lazyLoad($this->isVariation, function () {
            return $this->getWCProduct()->is_type('variation');
        });
    }
    public function isDifferentTypeProduct(): bool
    {
        $this->initIsDifferentTypeProduct();
        return $this->isDifferntTypeProduct;
    }
    protected function initIsDifferentTypeProduct(): void
    {
        $this->lazyLoad($this->isDifferntTypeProduct, function () {
            return !$this->isSimpleProduct() && !$this->isVariableProduct() && !$this->isVariation();
        });
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
        $this->initFirstFullFitRule();
        return $this->firstFullFitRule;
    }

    protected function initFirstFullFitRule(): void
    {
        $this->lazyLoad($this->firstFullFitRule, function () {
            foreach ($this->getRules() as $rule) {
                if ($rule->doesQtyFits()) {
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

    public function getPriceDisplay(string $defaultPriceHtml, bool $isInLoop): PriceDisplay
    {
        $this->initPriceDisplay($defaultPriceHtml, $isInLoop);
        return $this->priceDisplay;
    }

    protected function initPriceDisplay(string $defaultPriceHtml, bool $isInLoop): void
    {
        $this->lazyLoad($this->priceDisplay, function () use ($defaultPriceHtml, $isInLoop) {
            return new PriceDisplay($this, $defaultPriceHtml, $isInLoop);
        });
    }


    protected function getRuleQueryArgs(): array
    {
        $usersController = UsersController::getInstance();
        $currentUser = $usersController->getCurrentUser();

        $params = [
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

        $params['meta_query']['user_type_clause'] = [
            'relation' => 'OR',
            [
                'key' => Prefixer::getPrefixedMeta('user_type'),
                'value' => $currentUser->isB2b() ? ['b2b', 'b2x'] : ['b2c', 'b2x'],
                'compare' => 'IN',
            ],
            [
                'key' => Prefixer::getPrefixedMeta('user_type'),
                'compare' => 'NOT EXISTS',
            ],
        ];


        return $params;
    }
}
