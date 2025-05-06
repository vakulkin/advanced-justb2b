<?php

namespace JustB2b\Fields;

defined('ABSPATH') || exit;

class AssociationProductsField extends AssociationPostsField
{

    public function __construct(string $key, string $label)
    {
        parent::__construct($key, $label);
        $this->setTypes([
            [
                'type' => 'post',
                'post_type' => 'product',
            ],
            // [
            //     'type' => 'post',
            //     'post_type' => 'product_variation',
            // ]
        ]);
    }

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
}
