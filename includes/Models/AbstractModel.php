<?php

namespace JustB2b\Models;

use JustB2b\Traits\RuntimeCacheTrait;

defined('ABSPATH') || exit;

abstract class AbstractModel
{
    use RuntimeCacheTrait;

    abstract public static function getFieldsDefinition(): array;

    public function getField(string $key): ?object
    {
        foreach (static::getFieldsDefinition() as $field) {
            if ($key == $field->getKey()) {
                return $field;
            }
        }

        return null;
    }

    abstract public function getFieldValue(string $key): mixed;

    protected function cacheContext(array $extra = []): array
    {
        return $extra;
    }
}
