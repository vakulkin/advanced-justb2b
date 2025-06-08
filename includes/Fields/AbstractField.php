<?php

namespace JustB2b\Fields;

defined('ABSPATH') || exit;

use Carbon_Fields\Field\Field;
use JustB2b\Utils\Prefixer;
use JustB2b\Traits\RuntimeCacheTrait;

abstract class AbstractField
{
    use RuntimeCacheTrait;

    protected string $type;
    protected string $key;
    protected string $label;
    protected string $prefixedKey;
    protected int $width = 100;
    protected array $attributes = [];
    protected mixed $defaultValue = '';
    protected string $helpText = '';
    protected string $sectionName = 'Main';

    public function __construct(string $key, string $label)
    {
        $this->key = $key;
        $this->label = $label;
        $this->prefixedKey = Prefixer::getPrefixed($key);
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

    public function setHelpText(string $text): static
    {
        $this->helpText = $text;
        return $this;
    }

    public function getHelpText(): ?string
    {
        return $this->helpText;
    }

    public function getSectionName(): string
    {
        return $this->sectionName;
    }

    public function setSectionName(string $name): static
    {
        $this->sectionName = $name;
        return $this;
    }

    public function toCarbonField(): Field
    {
        $field = Field::make($this->type, $this->prefixedKey, $this->label)
            ->set_width($this->width);

        foreach ($this->attributes as $attr => $val) {
            $field->set_attribute($attr, $val);
        }

        if (isset($this->helpText)) {
            $field->set_help_text($this->helpText);
        }

        return $field;
    }

    public function getPostFieldOriginValue(int $postId): mixed
    {
        error_log(print_r([
            'post_id' => $postId,
            'key' => $this->prefixedKey,
            'value' => carbon_get_post_meta($postId, $this->prefixedKey)
        ], true));
        
        return self::getFromRuntimeCache(
            fn () => carbon_get_post_meta($postId, $this->prefixedKey),
            ['post_id' => $postId, 'key' => $this->prefixedKey]
        );
    }

    public function getUserFieldOriginValue(int $userId): mixed
    {
        return self::getFromRuntimeCache(
            fn () => carbon_get_user_meta($userId, $this->prefixedKey),
            ['user_id' => $userId, 'key' => $this->prefixedKey]
        );

    }

    public function getOptionOriginValue(): mixed
    {
        return self::getFromRuntimeCache(
            fn () => carbon_get_theme_option($this->prefixedKey),
            ['key' => $this->prefixedKey]
        );
    }
    public function isPostFieldEmpty(int $postId): bool
    {
        return $this->isEmpty($this->getPostFieldOriginValue($postId));
    }

    public function isUserFieldEmpty(int $postId): bool
    {
        return $this->isEmpty($this->getUserFieldOriginValue($postId));
    }

    public function isOptionEmpty(): bool
    {
        return $this->isEmpty($this->getOptionOriginValue());
    }

    protected function isEmpty($value): bool
    {
        return $value === null;
    }

    protected function resolveFieldValue(mixed $value, mixed $default): mixed
    {
        return empty($value) ? $default : $value;
    }

    public function getPostFieldValue(int $postId): mixed
    {
        $value = $this->getPostFieldOriginValue($postId);
        return $this->resolveFieldValue($value, $this->defaultValue);
    }

    public function getUserFieldValue(int $userId): mixed
    {
        $value = $this->getUserFieldOriginValue($userId);
        return $this->resolveFieldValue($value, $this->defaultValue);
    }

    public function getOptionValue(): mixed
    {
        $value = $this->getOptionOriginValue();
        return $this->resolveFieldValue($value, $this->defaultValue);
    }

    public function renderValue(int $parentId): string
    {
        $values = $this->getPostFieldValue($parentId);
        return $values;
    }

}
