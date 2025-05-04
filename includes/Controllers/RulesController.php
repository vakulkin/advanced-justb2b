<?php

namespace JustB2b\Controllers;

use JustB2b\Fields\NumberField;


defined('ABSPATH') || exit;

use Carbon_Fields\Container;
use JustB2b\Models\RuleModel;
use JustB2b\Fields\FieldBuilder;
use JustB2b\Fields\Definitions\RulesFieldsDefinition;


class RulesController extends BaseCustomPostController
{
    protected static string $modelClass = RuleModel::class;

    public function __construct()
    {
        parent::__construct();
        $this->maybeRegisterAdminColumns();
    }

    public function registerFields()
    {
        $definitions = RulesFieldsDefinition::getMainFields();
        $fields = FieldBuilder::buildFields($definitions);

        Container::make('post_meta', 'JustB2B')
            ->where('post_type', '=', self::$modelClass::getPrefixedKey())
            ->set_context('side')
            ->set_priority('default')
            ->add_fields($fields);

        $definitions = RulesFieldsDefinition::getMainConditions();
        $fields = FieldBuilder::buildFields($definitions);

        Container::make('post_meta', 'Main Conditions')
            ->where('post_type', '=', self::$modelClass::getPrefixedKey())
            ->add_fields($fields);

        $definitions = RulesFieldsDefinition::getQualifyingConditions();
        $fields = FieldBuilder::buildFields(definitions: $definitions);

        Container::make('post_meta', 'Qualifying Conditions')
            ->where('post_type', '=', self::$modelClass::getPrefixedKey())
            ->add_fields($fields);

        $definitions = RulesFieldsDefinition::getExcludingConditions();
        $fields = FieldBuilder::buildFields($definitions);

        Container::make('post_meta', 'Excluding Conditions')
            ->where('post_type', '=', self::$modelClass::getPrefixedKey())
            ->add_fields($fields);
    }

    protected function maybeRegisterAdminColumns(): void
    {
        $fields = array_merge(
            RulesFieldsDefinition::getMainFields(),
            RulesFieldsDefinition::getMainConditions(),
            RulesFieldsDefinition::getQualifyingConditions(),
            RulesFieldsDefinition::getExcludingConditions()
        );

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

        // Register sortable columns
        add_filter("manage_edit-{$postType}_sortable_columns", function ($columns) use ($fields) {
            foreach ($fields as $field) {
                if ('number' === $field->getAttribute('type')) {
                    $columns[$field->getKey()] = $field->getPrefixedKey();
                }
            }
            return $columns;
        });

        // Handle sorting logic
        add_action('pre_get_posts', function (\WP_Query $query) use ($fields, $postType) {
            if (!is_admin() || !$query->is_main_query()) {
                return;
            }

            if ($query->get('post_type') !== $postType) {
                return;
            }

            $orderby = $query->get('orderby');
            foreach ($fields as $field) {
                if (
                    $field->getKey() === $orderby &&
                    'number' === $field->getAttribute('type')
                ) {
                    $query->set('meta_key', $field->getPrefixedKey());
                    $query->set('orderby', 'meta_value_num');
                    break;
                }
            }
        });

        add_filter('default_hidden_columns', function ($hidden, $screen) use ($fields, $postType) {
            if ($screen->id === "edit-{$postType}") {
                foreach ($fields as $field) {
                    $key = $field->getKey();
                    if (!in_array($key, $hidden, true)) {
                        $hidden[] = $key;
                    }
                }
            }
            return $hidden;
        }, 10, 2);
    }

}