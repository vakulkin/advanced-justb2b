<?php

namespace JustB2b\Fields;

defined('ABSPATH') || exit;

class AssociationTermsField extends AssociationField
{
    public function __construct(string $key, string $label)
    {
        parent::__construct($key, $label);

        $this->setTypes([
            [
                'type' => 'term',
                'taxonomy' => 'product_cat',
            ],
            [
                'type' => 'term',
                'taxonomy' => 'product_tag',
            ],
        ]);
    }

    public static function getValues(int $parentId, string $key): false|array
    {
        $terms = carbon_get_post_meta($parentId, $key);
        $result = [];
        if (is_array($terms)) {
            foreach ($terms as $termData) {
                $termId = (int) ($termData['id'] ?? 0);

                if ($termId && ($term = get_term($termId)) && !is_wp_error($term)) {
                    $result[$term->term_id] = [
                        'id' => $term->term_id,
                        'taxonomy' => $term->taxonomy,
                    ];
                    continue;
                }
                return false;
            }
        }

        return $result;
    }

    public static function renderValue(int $parentId, string $key): string
    {
        $values = self::getValues($parentId, $key);

        if (!$values) {
            return '<div class="justb2b-associations justb2b-empty">—</div>';
        }

        return self::renderEntities(
            $values,
            fn($id) => get_term($id),
            fn($term) => get_term_link($term),
            fn($term) => $term->name
        );
    }
}
