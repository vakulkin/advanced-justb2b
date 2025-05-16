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

    public function getPostFieldValue(int $postId): float
    {
        return (float) parent::getPostFieldValue($postId);
    }

    public function getUserFieldValue(int $userId): float
    {
        return (float) parent::getPostFieldValue($userId);
    }

    public function getOptionValue(): float
    {
        return (float) parent::getOptionValue();
    }
}
