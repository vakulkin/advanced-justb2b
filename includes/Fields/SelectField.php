<?php

namespace JustB2b\Fields;

defined('ABSPATH') || exit;

use Carbon_Fields\Field\Field;
use JustB2b\Fields\BaseField;

class SelectField extends BaseField
{
    protected array $options = [];

    public function setOptions(array $options): static
    {
        $this->options = $options;
        return $this;
    }

    public function toCarbonField(): Field
    {
        $field = Field::make('select', $this->prefixedKey, $this->label)
            ->add_options($this->options)
            ->set_width($this->width);

        foreach ($this->attributes as $attr => $val) {
            $field->set_attribute($attr, $val);
        }

        return $field;
    }
}
