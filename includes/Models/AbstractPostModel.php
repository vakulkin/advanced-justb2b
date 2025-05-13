<?php

namespace JustB2b\Models;

use JustB2b\Utils\Prefixer;
use JustB2b\Fields\AbstractField;
use JustB2b\Traits\RuntimeCacheTrait;

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

    public function getTitle(): string
    {
        return $this->getFromRuntimeCache("post_title_{$this->id}", fn() => get_the_title($this->id));
    }

    public static function getKey(): string
    {
        return static::$key;
    }

    public static function getPrefixedKey(): string
    {
        return Prefixer::getPrefixed(static::getKey());
    }

    public function getFieldValue(string $key): mixed
    {
        /** @var AbstractField $field */
        $field = $this->getField($key);
        return $field ? $field->getPostFieldValue($this->id) : false;
    }
}
