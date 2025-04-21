<?php

namespace JustB2b\Fields;

defined('ABSPATH') || exit;

use Carbon_Fields\Field\Field;
use JustB2b\Utils\Prefixer;

abstract class BaseField {
    protected string $type;
    protected string $key;
    protected string $prefixedKey;
    protected string $label;
    protected int $width = 100;
    protected array $attributes = [];
    protected string $defaultValue;

    public function __construct(string $key, string $label) {
        $this->key = $key;
        $this->prefixedKey = Prefixer::getPrefixed($key);
        $this->label = $label;
    }

    public function setWidth(int $width): static {
        $this->width = $width;
        return $this;
    }

    public function setAttribute(string $name, mixed $value): static {
        $this->attributes[$name] = $value;
        return $this;
    }

    public function setDefaultValue($value) {
        $this->defaultValue = $value;
    }

    public function getKey(): string {
        return $this->key;
    }

    public function getPrefixedKey(): string {
        return $this->prefixedKey;
    }

    public function getLabel(): string {
        return $this->label;
    }

    public function getWidth(): int {
        return $this->width;
    }

    public function getAttributes(): array {
        return $this->attributes;
    }

    public function toCarbonField(): Field
    {
        $field = Field::make($this->type, $this->prefixedKey, $this->label);
        $field->set_width($this->width);

        foreach ($this->attributes as $attr => $val) {
            $field->set_attribute($attr, $val);
        }

        if (!empty($this->defaultValue)) {
            $field->set_default_value($this->defaultValue);
        }

        return $field;
    }
}
