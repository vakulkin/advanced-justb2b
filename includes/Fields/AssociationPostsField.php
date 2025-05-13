<?php

namespace JustB2b\Fields;

defined('ABSPATH') || exit;

abstract class AssociationPostsField extends AssociationField
{
    /**
     * @return array<int, array>|false
     */
    public function getPostFieldValue(int $parentId): false|array
    {
        $posts = parent::getPostFieldValue($parentId);
        $published = [];
        if (is_array($posts)) {
            foreach ($posts as $post) {
                $post['id'] = (int) $post['id'] ?? 0;
                if ($post['id'] && get_post_status($post['id']) === 'publish') {
                    $published[$post['id']] = $post;
                    continue;
                }
                return false;
            }
        }
        return $published;
    }

    public function renderValue(int $parentId): string
    {
        $values = $this->getPostFieldValue($parentId);

        return $this->renderEntities(
            $values,
            fn($id) => get_post($id),
            fn($post) => get_permalink($post),
            fn($post) => $post->post_title
        );
    }
}
