<?php

namespace JustB2b\Fields;

use JustB2b\Utils\Prefixer;

defined('ABSPATH') || exit;

abstract class BaseField {
    protected string $key;
    protected string $prefixedKey;
    protected string $label;
    protected int $width = 100;
    protected array $attributes = [];

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

    abstract public function toCarbonField(): \Carbon_Fields\Field\Field;
}
