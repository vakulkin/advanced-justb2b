<?php

namespace JustB2b\Fields;

defined('ABSPATH') || exit;

use Carbon_Fields\Field\Field;

class NonNegativeNumberField extends NumberField
{
    public function toCarbonField(): Field
    {
        $field = parent::toCarbonField();
        $field->set_attribute('min', 0);
        return $field;
    }

    public function getPostFieldValue(int $postId): float
    {
        return abs(parent::getPostFieldValue($postId));
    }

    public function getUserFieldValue(int $userId): float
    {
        return abs(parent::getPostFieldValue($userId));
    }

    public function getOptionValue(): float
    {
        return abs(parent::getOptionValue());
    }
}
