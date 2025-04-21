<?php

namespace JustB2b\Fields;

defined('ABSPATH') || exit;

use Carbon_Fields\Field\Field;

class AssociationField extends BaseField
{
    protected string $type = 'association';
    protected array $postTypes = [];

    public function setTypes(array $postTypes): static
    {
        $this->postTypes = $postTypes;
        return $this;
    }

    public function toCarbonField(): Field
    {
        $field = Field::make($this->type, $this->prefixedKey, $this->label);

        $field->set_types($this->postTypes);
        $field->set_width($this->width);

        foreach ($this->attributes as $attr => $val) {
            $field->set_attribute($attr, $val);
        }

        return $field;
    }
}
