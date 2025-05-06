<?php

namespace JustB2b\Controllers;

use Carbon_Fields\Container;
use JustB2b\Fields\AssociationField;
use JustB2b\Models\RuleModel;
use JustB2b\Fields\FieldBuilder;
use JustB2b\Fields\AssociationProductsField;
use JustB2b\Fields\AssociationRolesField;
use JustB2b\Fields\AssociationTermsField;
use JustB2b\Fields\RichText;
use JustB2b\Fields\TextField;
use JustB2b\Fields\SelectField;
use JustB2b\Fields\AssociationUsersField;


defined('ABSPATH') || exit;


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
        $definitions = self::getMainFields();
        $fields = FieldBuilder::buildFields($definitions);

        Container::make('post_meta', 'JustB2B')
            ->where('post_type', '=', self::$modelClass::getPrefixedKey())
            ->set_context('side')
            ->set_priority('default')
            ->add_fields($fields);

        $definitions = self::getMainConditions();
        $fields = FieldBuilder::buildFields($definitions);

        Container::make('post_meta', 'Main Conditions')
            ->where('post_type', '=', self::$modelClass::getPrefixedKey())
            ->add_fields($fields);

        $definitions = self::getQualifyingConditions();
        $fields = FieldBuilder::buildFields(definitions: $definitions);

        Container::make('post_meta', 'Qualifying Conditions')
            ->where('post_type', '=', self::$modelClass::getPrefixedKey())
            ->add_fields($fields);

        $definitions = self::getExcludingConditions();
        $fields = FieldBuilder::buildFields($definitions);

        Container::make('post_meta', 'Excluding Conditions')
            ->where('post_type', '=', self::$modelClass::getPrefixedKey())
            ->add_fields($fields);
    }

    protected function maybeRegisterAdminColumns(): void
    {
        $fields = array_merge(
            self::getMainFields(),
            self::getMainConditions(),
            self::getQualifyingConditions(),
            self::getExcludingConditions()
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
                        ? $field->renderInstanceValue($postId)
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

        // add_filter('default_hidden_columns', function ($hidden, $screen) use ($fields, $postType) {
        //     if ($screen->id === "edit-{$postType}") {
        //         foreach ($fields as $field) {
        //             $key = $field->getKey();
        //             if (!in_array($key, $hidden, true)) {
        //                 $hidden[] = $key;
        //             }
        //         }
        //     }
        //     return $hidden;
        // }, 10, 2);
    }

    protected static function getPrimaryPriceSources(): array
    {
        return [
            '_price' => '_price',
            '_regular_price' => '_regular_price',
            '_sale_price' => '_sale_price',
            'rrp_price' => 'rrp_price',
            'base_price_1' => 'base_price_1',
            'base_price_2' => 'base_price_2',
            'base_price_3' => 'base_price_3',
            'base_price_4' => 'base_price_4',
            'base_price_5' => 'base_price_5',
        ];
    }

    protected static function getSecondaryPriceSources(): array
    {
        return [
            'disabled' => 'disabled',
        ] + self::getPrimaryPriceSources();
    }

    public static function getMainFields(): array
    {
        return [
            (new TextField('priority', 'Priority'))
                ->setAttribute('type', 'number')
                ->setAttribute('step', 'any')
                ->setWidth(50),

            (new SelectField('user_type', 'User type'))
                ->setOptions([
                    'b2x' => 'b2x',
                    'b2b' => 'b2b',
                    'b2c' => 'b2c',
                ])
                ->setWidth(50),

            (new SelectField('visibility', 'Visibility'))
                ->setOptions([
                    'show' => 'show',
                    'loop_hidden' => 'loop_hidden',
                    'fully_hidden' => 'fully_hidden',
                ])
                ->setWidth(50),

            (new SelectField('primary_price_source', 'Primary price source'))
                ->setOptions(self::getPrimaryPriceSources())
                ->setWidth(50),

            (new SelectField('secondary_price_source', 'Secondary price source'))
                ->setOptions(self::getSecondaryPriceSources())
                ->setWidth(50),

            (new SelectField('secondary_rrp_source', 'Secondary RPP source'))
                ->setOptions(self::getSecondaryPriceSources())
                ->setWidth(50),

            (new SelectField('kind', 'Rodzaj'))
                ->setOptions([
                    'price_source' => 'price_source',
                    'net_minus_percent' => 'net_minus_percent',
                    'net_plus_percent' => 'net_plus_percent',
                    'net_minus_number' => 'net_minus_number',
                    'net_plus_number' => 'net_plus_number',
                    'net_equals_number' => 'net_equals_number',
                    'gross_minus_percent' => 'gross_minus_percent',
                    'gross_plus_percent' => 'gross_plus_percent',
                    'gross_minus_number' => 'gross_minus_number',
                    'gross_plus_number' => 'gross_plus_number',
                    'gross_equals_number' => 'gross_equals_number',
                    'non_purchasable' => 'non_purchasable',
                    'non_purchasable_hide_price' => 'non_purchasable_hide_price',
                    'zero_order_for_price' => 'zero_order_for_price',
                ])
                ->setWidth(50),

            (new TextField('value', 'Wartość'))
                ->setAttribute('type', 'number')
                ->setAttribute('step', 'any')
                ->setWidth(50),

            (new TextField('min_qty', 'Min ilość'))
                ->setAttribute('type', 'number')
                ->setAttribute('step', 'any')
                ->setWidth(50),

            (new TextField('max_qty', 'Max ilość'))
                ->setAttribute('type', 'number')
                ->setAttribute('step', 'any')
                ->setWidth(50),

            (new SelectField('show_in_qty_table', 'Pokazać w tabeli'))
                ->setOptions([
                    'show' => 'show',
                    'hide' => 'hide',
                ])
                ->setWidth(50),
            (new RichText('custom_html_1', 'custom_html_1'))
                ->setWidth(100),
        ];
    }

    public static function getMainConditions(): array
    {
        return [
            (new AssociationRolesField('roles', 'Roles')),
            (new AssociationUsersField('users', 'Users')),
            (new AssociationProductsField('products', 'Products')),
            (new AssociationTermsField('woo_terms', 'Woo Terms')),
        ];
    }

    public static function getQualifyingConditions(): array
    {
        return [
            (new AssociationRolesField('qualifying_roles', 'Qualifying Roles')),
            (new AssociationTermsField('qualifying_woo_terms', 'Qualifying Woo Terms')),
        ];
    }

    public static function getExcludingConditions(): array
    {
        return [
            (new AssociationRolesField('excluding_roles', 'Excluding Roles')),
            (new AssociationUsersField('excluding_users', 'Excluding Users')),
            (new AssociationProductsField('excluding_products', 'Excluding Products')),
            (new AssociationTermsField('excluding_woo_terms', 'Excluding Woo Terms')),
        ];
    }

}