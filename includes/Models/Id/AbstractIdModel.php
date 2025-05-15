<?php

namespace JustB2b\Models\Id;

use JustB2b\Models\AbstractModel;

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
}
