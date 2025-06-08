<?php

namespace JustB2b\Models\Id;

use JustB2b\Fields\AbstractField;
use JustB2b\Traits\RuntimeCacheTrait;
use JustB2b\Utils\Prefixer;

defined('ABSPATH') || exit;

abstract class AbstractPostModel extends AbstractIdModel
{
    use RuntimeCacheTrait;

    protected static string $key;

    abstract public static function getSingleName(): string;
    abstract public static function getPluralName(): string;

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

    public static function getKey(): string
    {
        return static::$key;
    }

    public static function getPrefixedKey(): string
    {
        return Prefixer::getPrefixed(static::getKey());
    }

    public function isEmptyField($key): bool {
        /** @var AbstractField $field */
        $field = $this->getField($key);
        return $field ? $field->isPostFieldEmpty($this->id) : true;
    }

    public function getFieldValue(string $key): mixed
    {
        /** @var AbstractField $field */
        $field = $this->getField($key);
        return $field ? $field->getPostFieldValue(postId: $this->id) : null;
    }
}
