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
}
