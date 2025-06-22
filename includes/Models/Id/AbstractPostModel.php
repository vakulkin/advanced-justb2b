<?php

namespace JustB2b\Models\Id;

use JustB2b\Traits\RuntimeCacheTrait;

defined('ABSPATH') || exit;

abstract class AbstractPostModel extends AbstractIdModel
{
    use RuntimeCacheTrait;

    public function __construct(int $id)
    {
        parent::__construct($id);
    }

    protected function cacheContext(array $extra = []): array
    {
        return array_merge(
            parent::cacheContext($extra),
            ['post_id' => $this->id],
        );
    }

    public function getTitle(): string
    {
        return self::getFromRuntimeCache(
            fn () => get_the_title($this->id),
            $this->cacheContext()
        );
    }
}
