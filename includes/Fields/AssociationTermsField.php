<?php

namespace JustB2b\Fields;

defined('ABSPATH') || exit;

class AssociationTermsField extends AssociationField
{
    public function __construct(string $key, string $label)
    {
        parent::__construct($key, $label);

        $this->setPostTypes([
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

    public function getPostFieldValue(int $parentId): false|array
    {
        $terms = parent::getPostFieldValue($parentId);
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

    public function renderValue(int $parentId): string
    {
        $values = $this->getPostFieldValue($parentId);

        return $this->renderEntities(
            $values,
            fn($id) => get_term($id),
            fn($term) => get_term_link($term),
            fn($term) => $term->name
        );
    }
}
