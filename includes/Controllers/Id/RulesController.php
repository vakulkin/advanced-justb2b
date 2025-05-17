<?php

namespace JustB2b\Controllers\Id;

use Carbon_Fields\Container;
use JustB2b\Fields\AbstractField;
use JustB2b\Models\Id\RuleModel;
use JustB2b\Fields\FieldBuilder;

defined('ABSPATH') || exit;

class RulesController extends AbstractCustomPostController
{
    protected function __construct()
    {
        parent::__construct();
        $this->registerAdminColumns();
    }

    public function getSingleName(): string
    {
        return RuleModel::getSingleName();
    }

    public function getPluralName(): string
    {
        return RuleModel::getPluralName();
    }

    public function getPrefixedKey(): string
    {
        return RuleModel::getPrefixedKey();
    }


    public function registerCarbonFields()
    {
        $fields = FieldBuilder::buildFields(RuleModel::getFieldsDefinition());
        Container::make('post_meta', 'JustB2B')
            ->where('post_type', '=', RuleModel::getPrefixedKey())
            ->add_fields($fields);
    }

    protected function registerAdminColumns(): void
    {
        $fields = RuleModel::getFieldsDefinition();
        $postType = RuleModel::getPrefixedKey();

        add_filter("manage_edit-{$postType}_columns", function ($columns) use ($fields) {
            /** @var AbstractField $field */
            foreach ($fields as $field) {
                $columns[$field->getKey()] = $field->getLabel();
            }
            return $columns;
        });

        add_action("manage_{$postType}_posts_custom_column", function ($column, $postId) use ($fields) {
            foreach ($fields as $field) {
                /** @var AbstractField $field */
                if ($column === $field->getKey()) {
                    echo $field->renderValue($postId);
                    return;
                }
            }
        }, 10, 2);

        add_filter("manage_edit-{$postType}_sortable_columns", function ($columns) use ($fields) {
            /** @var AbstractField $field */
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
                /** @var AbstractField $field */
                if ($field->getKey() === $orderby && $field->getAttribute('type') === 'number') {
                    $query->set('meta_key', $field->getPrefixedKey());
                    $query->set('orderby', 'meta_value_num');
                    break;
                }
            }
        });
    }


}
