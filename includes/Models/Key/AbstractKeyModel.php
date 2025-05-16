<?php

namespace JustB2b\Models\Key;

use JustB2b\Models\AbstractModel;
use JustB2b\Fields\AbstractField;

defined('ABSPATH') || exit;

abstract class AbstractKeyModel extends AbstractModel
{
    abstract public function getKey(): string;


    public function isEmptyField($key): bool
    {
        /** @var AbstractField $field */
        $field = $this->getField($key);
        return $field ? $field->isOptionEmpty() : true;
    }

    public function getFieldValue(string $key): mixed
    {
        /** @var AbstractField $field */
        $field = $this->getField($key);
        return $field ? $field->getOptionValue() : null;
    }
}
