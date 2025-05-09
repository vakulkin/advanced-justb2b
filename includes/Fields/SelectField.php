<?php

namespace JustB2b\Fields;

defined('ABSPATH') || exit;

use Carbon_Fields\Field\Field;
use JustB2b\Fields\BaseField;

class SelectField extends BaseField
{
    protected string $type = 'select';
    protected array $options = [];

    public function setOptions(array $options): static
    {
        $this->options = $options;
        return $this;
    }

    public function toCarbonField(): Field
    {
        /** @var Field $field */
        $field = parent::toCarbonField();
        if (!empty($this->options)) {
            $field->add_options($this->options);
        }

        return $field;
    }
}
