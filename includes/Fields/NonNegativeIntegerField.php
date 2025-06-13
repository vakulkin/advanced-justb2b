<?php

namespace JustB2b\Fields;

defined('ABSPATH') || exit;

use Carbon_Fields\Field\Field;

class NonNegativeIntegerField extends NonNegativeNumberField
{
    public function toCarbonField(): Field
    {
        $field = parent::toCarbonField();
        $field->set_attribute('step', '1');
        return $field;
    }

    public function getPostFieldValue(int $postId): float
    {
        return (int) parent::getPostFieldValue($postId);
    }

    public function getUserFieldValue(int $userId): float
    {
        return (int) parent::getPostFieldValue($userId);
    }

    public function getOptionValue(): float
    {
        return (int) parent::getOptionValue();
    }
}
