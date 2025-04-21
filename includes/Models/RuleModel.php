<?php

namespace JustB2b\Models;

use JustB2b\Utils\Pricing\PriceCalculator;

defined('ABSPATH') || exit;

use JustB2b\Utils\Prefixer;

class RuleModel extends BasePostModel
{
    protected bool $isFits;
    protected int $priority;
    protected string $kind;
    protected string $startPriceSource;
    protected float $value;
    protected int $minQty;
    protected int $maxQty;
    protected bool $showInQtyTable;

    protected static string $key = 'rule';

    public function __construct(int $id, int $conditionProductId = null, int $conditionUserId = null)
    {
        parent::__construct($id);
        $this->initFit($conditionProductId, $conditionUserId);
        $this->initPriority();
        $this->initKind();
        $this->initStartPriceSource();
        $this->initValue();
        $this->initMinQty();
        $this->initMaxQty();
        $this->initShowInQtyTable();
    }

    public function getPriority(): int
    {
        return $this->priority;
    }

    protected function initPriority(): void
    {
        $this->priority = (int) carbon_get_post_meta(
            $this->id,
            Prefixer::getPrefixed('priority')
        );
    }

    public function getKind(): string
    {
        return $this->kind;
    }

    protected function initKind(): void
    {
        $this->kind = get_post_meta(
            $this->id,
            Prefixer::getPrefixedMeta('kind'),
            true
        );
    }

    public function getStartPriceSource(): string
    {
        return $this->startPriceSource;
    }

    protected function initStartPriceSource(): void
    {
        $startPriceSource = get_post_meta(
            $this->id,
            Prefixer::getPrefixedMeta('start_price'),
            true
        ) ?: '_price';

        $this->startPriceSource = $startPriceSource;
    }

    public function getValue(): float
    {
        return $this->value;
    }

    protected function initValue(): void
    {
        $value = carbon_get_post_meta($this->id, Prefixer::getPrefixed('value'));
        $this->value = abs(PriceCalculator::getFloat($value));
    }

    public function getMinQty(): int
    {
        return $this->minQty;
    }

    protected function initMinQty(): void
    {
        $this->minQty = (int) carbon_get_post_meta(
            $this->id,
            Prefixer::getPrefixed('min_qty')
        );
    }

    public function getMaxQty(): int
    {
        return $this->maxQty;
    }

    protected function initMaxQty(): void
    {
        $this->maxQty = (int) carbon_get_post_meta(
            $this->id,
            Prefixer::getPrefixed('max_qty')
        );
    }

    public function showInQtyTable(): bool
    {
        return $this->showInQtyTable;
    }

    protected function initShowInQtyTable(): void
    {
        $showInQtyTable = get_post_meta(
            $this->id,
            Prefixer::getPrefixedMeta('show_in_qty_table'),
            true
        );
        // var_dump($showInQtyTable);
        $this->showInQtyTable = $showInQtyTable !== 'hide';
    }

    protected function initFit(int $conditionProductId = null, int $conditionUserId = null)
    {
        $this->isFits = $this->checkRoles($conditionUserId) &&
            ($this->checkProduct($conditionProductId)
                || $this->checkTerms($conditionProductId));
    }

    public function isFits(): bool
    {
        return $this->isFits;
    }


    protected function checkProduct(int $productId = null): bool
    {
        $products = self::getAssociatedPosts($this->id, Prefixer::getPrefixed('products'));
        return isset($products[$productId]);
    }

    protected function checkTerms(int $productId = null)
    {
        $terms = self::getAssociatedTerms($this->id, Prefixer::getPrefixed('woo_terms'));
        foreach ($terms as $term) {
            if (has_term($term['id'], $term['taxonomy'], $productId)) {
                return true;
            }
        }
        return false;
    }

    protected function checkRoles(int $userId = null): bool
    {
        return true; // TODO: Implement checkRoles() method.
    }

}
