<?php

namespace JustB2b\Controllers;

use Carbon_Fields\Container;
use JustB2b\Models\RoleModel;
use JustB2b\Fields\FieldBuilder;
use JustB2b\Fields\AssociationUsersField;

defined('ABSPATH') || exit;

class RolesController extends BaseCustomPostController
{
    protected static string $modelClass = RoleModel::class;

    protected function __construct()
    {
        parent::__construct();
        $this->maybeRegisterAdminColumns();
    }

    public function registerCarbonFields()
    {
        $definitions = self::getUsersFields();
        $fields = FieldBuilder::buildFields($definitions);

        Container::make('post_meta', 'JustB2B')
            ->where('post_type', '=', self::$modelClass::getPrefixedKey())
            ->add_fields($fields);
    }

    public static function getUsersFields(): array
    {
        return [
            new AssociationUsersField('users', 'Users'),
        ];
    }

    protected function maybeRegisterAdminColumns(): void
    {
        $fields = self::getUsersFields();
        if (empty($fields)) {
            return;
        }

        $postType = static::$modelClass::getPrefixedKey();

        add_filter("manage_edit-{$postType}_columns", function ($columns) use ($fields) {
            foreach ($fields as $field) {
                $columns[$field->getKey()] = $field->getLabel();
            }
            return $columns;
        });

        add_action("manage_{$postType}_posts_custom_column", function ($column, $postId) use ($fields) {
            foreach ($fields as $field) {
                if ($column === $field->getKey()) {
                    $value = carbon_get_post_meta($postId, $field->getPrefixedKey());
                    echo is_array($value)
                        ? json_encode($value)
                        : esc_html($value !== '' ? $value : '—');
                }
            }
        }, 10, 2);
    }
}
