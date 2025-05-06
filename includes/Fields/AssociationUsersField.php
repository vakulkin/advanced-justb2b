<?php

namespace JustB2b\Fields;

defined('ABSPATH') || exit;

class AssociationUsersField extends AssociationField
{

    public function __construct(string $key, string $label)
    {
        parent::__construct($key, $label);
        $this->setTypes([
            [
                'type' => 'user',
            ]
        ]);
    }

    public static function getValues(int $parentId, string $key): false|array
    {
        $users = carbon_get_post_meta($parentId, $key);
        $result = [];
        foreach ($users as $userData) {
            $userId = (int) ($userData['id'] ?? 0);
            if ($userId && ($user = get_userdata($userId))) {
                $result[$user->ID] = [
                    'id' => $user->ID,
                    'display_name' => $user->display_name,
                    'user_email' => $user->user_email,
                ];
            } else {
                return false;
            }
        }
        return $result;
    }

    public static function renderValue(int $parentId, string $key): string
    {
        $values = self::getValues($parentId, $key);
        return self::renderEntities(
            $values,
            fn($id) => get_userdata($id),
            fn($user) => get_author_posts_url($user->ID),
            fn($user) => $user->display_name
        );
    }
}
