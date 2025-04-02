<?php

namespace JustB2b\Fields;

defined('ABSPATH') || exit;

use Carbon_Fields\Field\Field;
use JustB2b\Fields\BaseField;

class TextField extends BaseField
{
    public function toCarbonField(): Field
    {
        $field = Field::make('text', $this->prefixedKey, $this->label)
            ->set_width($this->width);

        foreach ($this->attributes as $attr => $val) {
            $field->set_attribute($attr, $val);
        }

        return $field;
    }
}
