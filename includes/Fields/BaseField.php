<?php

namespace JustB2b\Fields;

defined('ABSPATH') || exit;

use Carbon_Fields\Field\Field;
use JustB2b\Utils\Prefixer;

abstract class BaseField
{
    protected string $type;
    protected string $key;
    protected string $prefixedKey;
    protected string $label;
    protected int $width = 100;
    protected array $attributes = [];
    protected mixed $defaultValue = null;
    protected string $helpText;

    public function __construct(string $key, string $label)
    {
        $this->key = $key;
        $this->prefixedKey = Prefixer::getPrefixed($key);
        $this->label = $label;
    }

    public function setWidth(int $width): static
    {
        $this->width = $width;
        return $this;
    }

    public function setAttribute(string $name, mixed $value): static
    {
        $this->attributes[$name] = $value;
        return $this;
    }

    public function getAttribute(string $name): mixed
    {
        return $this->attributes[$name] ?? null;
    }

    public function setDefaultValue(mixed $value): static
    {
        $this->defaultValue = $value;
        return $this;
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function getPrefixedKey(): string
    {
        return $this->prefixedKey;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function getWidth(): int
    {
        return $this->width;
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function setHelpText(string $text): static
    {
        $this->helpText = $text;
        return $this;
    }

    public function toCarbonField(): Field
    {
        $field = Field::make($this->type, $this->prefixedKey, $this->label)
            ->set_width($this->width);

        foreach ($this->attributes as $attr => $val) {
            $field->set_attribute($attr, $val);
        }

        if (isset($this->defaultValue)) {
            $field->set_default_value($this->defaultValue);
        }

        if (isset($this->helpText)) {
            $field->set_help_text($this->helpText);
        }

        return $field;
    }
}
