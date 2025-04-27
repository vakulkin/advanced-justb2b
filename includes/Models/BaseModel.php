<?php

namespace JustB2b\Models;

defined('ABSPATH') || exit;

abstract class BaseModel
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
