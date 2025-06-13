<?php

namespace JustB2b\Models\Id;

use JustB2b\Models\AbstractModel;
use JustB2b\Utils\Prefixer;

defined('ABSPATH') || exit;

abstract class AbstractIdModel extends AbstractModel
{
    protected int $id;

    public function __construct(int $id)
    {
        $this->initId($id);
    }

    public function getId(): int
    {
        return $this->id;
    }

    protected function initId(int $id): void
    {
        $this->id = $id;
    }

    protected function getUserTypeClause(bool $isB2b): array
    {
        $clauses = [
            'relation' => 'OR',
            [
                'key' => Prefixer::getPrefixedMeta('customer_type'),
                'value' => $isB2b ? ['b2b', 'b2x'] : ['b2c', 'b2x'],
                'compare' => 'IN',
            ],
        ];
        // todo: add empty logic

        return $clauses;
    }

    protected function getBaseMetaQuery(bool $isB2b): array
    {
        return [
            'relation' => 'AND',
            'priority_clause' => [
                'key' => Prefixer::getPrefixedMeta('priority'),
                'type' => 'NUMERIC',
            ],
            'customer_type_clause' => $this->getUserTypeClause($isB2b),
        ];
    }
}
