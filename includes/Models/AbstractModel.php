<?php

namespace JustB2b\Models;

defined('ABSPATH') || exit;

abstract class AbstractModel
{
    protected static array $fieldsCache = [];

    abstract public static function getFieldsDefinition(): array;

    public static function getFields(): array
    {
        $calledClass = static::class;

        if (!isset(static::$fieldsCache[$calledClass])) {
            static::$fieldsCache[$calledClass] = [];
            foreach (static::getFieldsDefinition() as $field) {
                static::$fieldsCache[$calledClass][$field->getKey()] = $field;
            }
        }

        return static::$fieldsCache[$calledClass];
    }


    public function getField(string $key): ?object
    {
        $fields = static::getFields();
        return $fields[$key] ?? null;
    }

    abstract public function getFieldValue(string $key): mixed;
}
