<?php

namespace JustB2b\Fields;

defined('ABSPATH') || exit;

use Carbon_Fields\Field\Field;

class NonNegativeNumberField extends NumberField
{
    public function toCarbonField(): Field
    {
        $field = parent::toCarbonField()
            ->set_attribute('min', 0);
        return $field;
    }
}
