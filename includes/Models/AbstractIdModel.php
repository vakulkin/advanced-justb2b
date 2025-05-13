<?php

namespace JustB2b\Models;

use JustB2b\Traits\LazyLoaderTrait;

defined('ABSPATH') || exit;

abstract class AbstractIdModel extends AbstractModel
{
    use LazyLoaderTrait;

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
