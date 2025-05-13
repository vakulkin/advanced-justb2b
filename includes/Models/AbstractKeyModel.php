<?php

namespace JustB2b\Models;

use JustB2b\Fields\AbstractField;

defined('ABSPATH') || exit;

abstract class AbstractKeyModel extends AbstractModel
{
    abstract public function getKey(): string;

    public function getFieldValue(string $key): mixed
    {
        /** @var AbstractField $field */
        $field = $this->getField($key);
        return $field ? $field->getOptionValue() : false;
    }
}
