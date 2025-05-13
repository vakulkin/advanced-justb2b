<?php

namespace JustB2b\Controllers;

use Carbon_Fields\Container;
use JustB2b\Models\RuleModel;
use JustB2b\Fields\FieldBuilder;

defined('ABSPATH') || exit;

class RulesController extends AbstractCustomPostController
{
    protected string $modelClass = RuleModel::class;

    protected function __construct()
    {
        parent::__construct();
        $this->maybeRegisterAdminColumns();
    }

    public function registerCarbonFields()
    {
        $fields = FieldBuilder::buildFields($this->modelClass::getFieldsDefinition());
        Container::make('post_meta', 'JustB2B')
            ->where('post_type', '=', $this->modelClass::getPrefixedKey())
            ->add_fields($fields);
    }

    protected function maybeRegisterAdminColumns(): void
    {
        $fields = $this->modelClass::getFieldsDefinition();

        if (empty($fields)) {
            return;
        }

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

        add_filter("manage_edit-{$postType}_sortable_columns", function ($columns) use ($fields) {
            foreach ($fields as $field) {
                if ($field->getAttribute('type') === 'number') {
                    $columns[$field->getKey()] = $field->getPrefixedKey();
                }
            }
            return $columns;
        });

        add_action('pre_get_posts', function (\WP_Query $query) use ($fields, $postType) {
            if (!is_admin() || !$query->is_main_query()) {
                return;
            }

            if ($query->get('post_type') !== $postType) {
                return;
            }

            $orderby = $query->get('orderby');
            foreach ($fields as $field) {
                if ($field->getKey() === $orderby && $field->getAttribute('type') === 'number') {
                    $query->set('meta_key', $field->getPrefixedKey());
                    $query->set('orderby', 'meta_value_num');
                    break;
                }
            }
        });
    }


}
