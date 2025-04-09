<?php

namespace JustB2b\Models;

use JustB2b\Utils\Pricing\PriceCalculator;

defined('ABSPATH') || exit;

use JustB2b\Utils\Prefixer;

class RuleModel extends BaseModel
{
    protected bool $isFits = false;
    protected int $priority;
    protected string $kind;
    protected string $startPriceSource;
    protected float $value;
    protected int $minQty;
    protected int $maxQty;
    protected string $showInTable;

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
        $this->initShowInTable();
    }

    public function isFits(): bool
    {
        return $this->isFits;
    }

    protected function initFit($conditionProductId, $conditionUserId)
    {
        $logicBlocks = self::getAssociatedPosts($this->id, Prefixer::getPrefixed('logic_blocks'));

        foreach ($logicBlocks as $logicBlockId => $logicBlock) {
            $logicBlockModelObject = new LogicBlockModel(
                $logicBlockId,
                $conditionProductId,
                $conditionUserId
            );

            if ($logicBlockModelObject->isFits()) {
                $this->isFits = true;
                break;
            }
        }
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

    public function showInTable(): string
    {
        return $this->showInTable;
    }

    protected function initShowInTable(): void
    {
        $this->showInTable = get_post_meta(
            $this->id,
            Prefixer::getPrefixedMeta('show_in_table'),
            true
        );
    }

}
