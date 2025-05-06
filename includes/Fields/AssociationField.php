<?php

namespace JustB2b\Fields;

defined('ABSPATH') || exit;

use Carbon_Fields\Field\Field;

abstract class AssociationField extends BaseField
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

    public function renderInstanceValue(int $parentId): string
    {
        return static::renderValue($parentId, $this->getPrefixedKey());
    }

    abstract public static function getValues(int $parentId, string $key): false|array;

    abstract public static function renderValue(int $parentId, string $key): string;

    protected static function renderEntities(array $values, callable $resolver, callable $linkGenerator, callable $labelGetter): string
    {
        $result = '<div class="justb2b-associations">';
        $visibleCount = 3;
        $count = 0;
        $total = count($values);

        foreach ($values as $value) {
            $id = (int) ($value['id'] ?? 0);
            $subtype = $value['subtype'] ?? $value['taxonomy'] ?? (isset($value['user_email']) ? 'user' : 'item');

            if ($id && ($entity = $resolver($id)) && !is_wp_error($entity)) {
                $count++;
                $label = esc_attr($labelGetter($entity));
                $url = esc_url($linkGenerator($entity));

                if ($count <= $visibleCount) {
                    $result .= "<a class=\"justb2b-association-field justb2b-{$subtype}-field-value\"
                            href=\"{$url}\" target=\"_blank\" rel=\"noopener noreferrer\"
                            title=\"{$label}\">{$label}</a>";
                } elseif ($count === $visibleCount + 1) {
                    $remaining = $total - $visibleCount;
                    $result .= "<span class=\"justb2b-association-field justb2b-{$subtype}-field-value\">+" . esc_html($remaining) . "</span>";
                    break;
                }
            }
        }

        $result .= '</div>';
        return $result;
    }

}
