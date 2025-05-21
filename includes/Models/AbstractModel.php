<?php

namespace JustB2b;

/*
Plugin Name:  Advanced JustB2B Plugin
Description: A plugin to manage B2B interactions with custom business rules, user roles, product groups, and pricing strategies.
Text Domain: justb2b
*/

use JustB2b\Fields\AbstractField;
use JustB2b\Traits\RuntimeCacheTrait;

defined('ABSPATH') || exit;

require_once __DIR__ . '/vendor/autoload.php';

abstract class AbstractModel
{
    use RuntimeCacheTrait;

    abstract public static function getFieldsDefinition(): array;

    public function getField(string $key): ?object
    {
        /** @var AbstractField $field */
        foreach (static::getFieldsDefinition() as $field) {
            if ($key == $field->getKey()) {
                return $field;
            }
        }
        return null;
    }

    abstract public function isEmptyField(string $key): bool;

    abstract public function getFieldValue(string $key): mixed;

    protected function cacheContext(array $extra = []): array
    {
        return $extra;
    }
}
