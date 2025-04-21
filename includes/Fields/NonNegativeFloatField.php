<?php

namespace JustB2b\Fields;

defined('ABSPATH') || exit;

use Carbon_Fields\Field\Field;

class NonNegativeFloatField extends NonNegativeNumberField
{
    public function toCarbonField(): Field
    {
        $field = parent::toCarbonField();
        $field->set_attribute('step', '0.01');
        return $field;
    }
}
