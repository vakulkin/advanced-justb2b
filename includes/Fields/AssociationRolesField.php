<?php

namespace JustB2b\Fields;

use JustB2b\Utils\Prefixer;

defined('ABSPATH') || exit;

class AssociationRolesField extends AssociationPostsField
{

    public function __construct(string $key, string $label)
    {
        parent::__construct($key, $label);
        $this->setTypes([
            [
                'type' => 'post',
                'post_type' => Prefixer::getPrefixed('role'),
            ]
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
