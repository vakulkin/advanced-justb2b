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
        return [
            'relation' => 'OR',
            [
                'key' => Prefixer::getPrefixedMeta('user_type'),
                'value' => $isB2b ? ['b2b', 'b2x'] : ['b2c', 'b2x'],
                'compare' => 'IN',
            ],
            [
                'key' => Prefixer::getPrefixedMeta('user_type'),
                'compare' => 'NOT EXISTS',
            ],
        ];
    }

    protected function getBaseMetaQuery(bool $isB2b): array
    {
        return [
            'relation' => 'AND',
            'priority_clause' => [
                'key' => Prefixer::getPrefixedMeta('priority'),
                'type' => 'NUMERIC',
            ],
            'user_type_clause' => $this->getUserTypeClause($isB2b),
        ];
    }
}
