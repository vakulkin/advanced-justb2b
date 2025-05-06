<?php

namespace JustB2b\Fields;

defined('ABSPATH') || exit;

abstract class AssociationPostsField extends AssociationField
{

    public static function getValues(int $parentId, string $key): false|array
    {
        $posts = carbon_get_post_meta($parentId, $key);
        $published = [];

        foreach ($posts as $post) {
            $postId = (int) ($post['id'] ?? 0);
            if (get_post_status($postId) === 'publish') {
                $published[$postId] = $post;
            } else {
                return false;
            }
        }

        return $published;
    }

    public static function renderValue(int $parentId, string $key): string
    {
        $values = self::getValues($parentId, $key);
        return self::renderEntities(
            $values,
            fn($id) => get_post($id),
            fn($post) => get_permalink($post),
            fn($post) => $post->post_title
        );
    }
}
