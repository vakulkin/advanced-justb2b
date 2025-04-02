<?php

namespace JustB2b\Fields;

defined('ABSPATH') || exit;

use Carbon_Fields\Field\Field;

class AssociationField extends BaseField {
    protected array $types = [];

    public function setTypes(array $types): static {
        $this->types = $types;
        return $this;
    }

    public function toCarbonField(): Field {
        $field = Field::make('association', $this->prefixedKey, $this->label)
                      ->set_types($this->types)
                      ->set_width($this->width);

        foreach ($this->attributes as $attr => $val) {
            $field->set_attribute($attr, $val);
        }

        return $field;
    }
}
