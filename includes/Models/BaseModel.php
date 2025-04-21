<?php

namespace JustB2b\Models;

defined('ABSPATH') || exit;

abstract class BaseModel
{
    protected int $id;

    public function __construct(int $id)
    {
        $this->id = $id;
    }

    public function getId(): int
    {
        return $this->id;
    }

}
