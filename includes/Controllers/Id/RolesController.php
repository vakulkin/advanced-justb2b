<?php

namespace JustB2b\Controllers\Id;

use Carbon_Fields\Container;
use JustB2b\Models\Id\RoleModel;
use JustB2b\Fields\FieldBuilder;

defined('ABSPATH') || exit;

class RolesController extends AbstractCustomPostController
{
    protected string $modelClass = RoleModel::class;

    protected function __construct()
    {
        parent::__construct();
        $this->maybeRegisterAdminColumns();
    }

    public function registerCarbonFields()
    {
        $definitions = $this->modelClass::getFieldsDefinition();
        $fields = FieldBuilder::buildFields($definitions);

        Container::make('post_meta', 'JustB2B')
            ->where('post_type', '=', $this->modelClass::getPrefixedKey())
            ->add_fields($fields);
    }

    protected function maybeRegisterAdminColumns(): void
    {
        $fields = $this->modelClass::getFieldsDefinition();

        $postType = $this->modelClass::getPrefixedKey();

        add_filter("manage_edit-{$postType}_columns", function ($columns) use ($fields) {
            foreach ($fields as $field) {
                $columns[$field->getKey()] = $field->getLabel();
            }
            return $columns;
        });

        add_action("manage_{$postType}_posts_custom_column", function ($column, $postId) use ($fields) {
            foreach ($fields as $field) {
                if ($column === $field->getKey()) {
                    $value = $field->getPostFieldValue($postId);
                    echo is_array($value)
                        ? $field->renderInstanceValue($postId)
                        : esc_html($value !== '' ? $value : '—');
                }
            }
        }, 10, 2);
    }
}
