<?php

namespace JustB2b\Fields;

defined('ABSPATH') || exit;

use Carbon_Fields\Field\Field;

class NumberField extends TextField
{
    public function toCarbonField(): Field
    {
        $field = parent::toCarbonField();
        $field->set_attribute('type', 'number');
        return $field;
    }

    public function __construct(string $key, string $label)
    {
        parent::__construct($key, $label);
        $this->defaultValue = 0;
    }

    public function getPostFieldValue(int $postId): mixed
    {
        $value = $this->getPostFieldOriginValue($postId);
        return $this->resolveFieldValue($value, $this->defaultValue);
    }

    public function getUserFieldValue(int $userId): mixed
    {
        $value = $this->getUserFieldOriginValue($userId);
        return $this->resolveFieldValue($value, $this->defaultValue);
    }

    public function getOptionValue(): mixed
    {
        $value = $this->getOptionOriginValue();
        return $this->resolveFieldValue($value, $this->defaultValue);
    }
}
