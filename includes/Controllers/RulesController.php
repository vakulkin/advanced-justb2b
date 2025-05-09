<?php

namespace JustB2b\Controllers;

use Carbon_Fields\Container;
use JustB2b\Fields\NumberField;
use JustB2b\Models\RuleModel;
use JustB2b\Fields\FieldBuilder;
use JustB2b\Fields\AssociationProductsField;
use JustB2b\Fields\AssociationRolesField;
use JustB2b\Fields\AssociationTermsField;
use JustB2b\Fields\AssociationUsersField;
use JustB2b\Fields\RichText;
use JustB2b\Fields\TextField;
use JustB2b\Fields\SelectField;

defined('ABSPATH') || exit;

class RulesController extends BaseCustomPostController
{
    protected static string $modelClass = RuleModel::class;

    protected function __construct()
    {
        parent::__construct();
        $this->maybeRegisterAdminColumns();
    }

    public function registerCarbonFields()
    {
        $containers = [
            ['label' => 'JustB2B', 'fields' => self::getMainFields()],
            ['label' => 'Main Conditions', 'fields' => self::getMainConditions()],
            ['label' => 'Qualifying Conditions', 'fields' => self::getQualifyingConditions()],
            ['label' => 'Excluding Conditions', 'fields' => self::getExcludingConditions()],
        ];

        foreach ($containers as $container) {
            $fields = FieldBuilder::buildFields($container['fields']);
            $c = Container::make('post_meta', $container['label'])
                ->where('post_type', '=', self::$modelClass::getPrefixedKey())
                ->add_fields($fields);

            if (isset($container['context'])) {
                $c->set_context($container['context']);
            }
            if (isset($container['priority'])) {
                $c->set_priority($container['priority']);
            }
        }
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
        return ['disabled' => 'disabled'] + self::getPrimaryPriceSources();
    }

    public static function getMainFields(): array
    {
        return [
            (new NumberField('priority', 'Priority'))
                ->setHelpText('Lower number = higher priority. Use gaps like 10, 20, 30. Defaults to 0.')
                ->setWidth(50),

            (new SelectField('user_type', 'User type'))
                ->setOptions(['b2x' => 'b2x', 'b2b' => 'b2b', 'b2c' => 'b2c'])
                ->setHelpText('Target user type. b2x means all users.')
                ->setWidth(50),

            (new SelectField('visibility', 'Visibility'))
                ->setOptions(['show' => 'show', 'fully_hidden' => 'fully_hidden'])
                ->setHelpText('Controls visibility. Fully hidden = not shown at all.')
                ->setWidth(50),

            (new SelectField('primary_price_source', 'Primary price source'))
                ->setOptions(self::getPrimaryPriceSources())
                ->setHelpText('Main price source used for calculation.')
                ->setWidth(50),

            (new SelectField('secondary_price_source', 'Secondary price source'))
                ->setOptions(self::getSecondaryPriceSources())
                ->setHelpText('Fallback if primary price is 0.')
                ->setWidth(50),

            (new SelectField('secondary_rrp_source', 'Secondary RPP source'))
                ->setOptions(self::getSecondaryPriceSources())
                ->setHelpText('Used if RRP is 0 or not set.')
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
                    'zero_order_for_price' => 'zero_order_for_price',
                ])
                ->setHelpText('How this rule changes the product price.')
                ->setWidth(50),

            (new TextField('value', 'Wartość'))
                ->setAttribute('type', 'number')
                ->setAttribute('step', 'any')
                ->setHelpText('Value used in price calculation.')
                ->setWidth(50),

            (new TextField('min_qty', 'Min ilość'))
                ->setAttribute('type', 'number')
                ->setAttribute('step', 'any')
                ->setHelpText('Min quantity to apply the rule. Defaults to 0.')
                ->setWidth(50),

            (new TextField('max_qty', 'Max ilość'))
                ->setAttribute('type', 'number')
                ->setAttribute('step', 'any')
                ->setHelpText('Max quantity to apply the rule. Empty = no limit.')
                ->setWidth(50),

            (new SelectField('all_prices_visibility', 'Prices visibility'))
                ->setOptions(['show' => 'show', 'hide' => 'hide', 'only_product' => 'only_product', 'only_loop' => 'only_loop'])
                ->setHelpText('Show/hide prices based on this rule.')
                ->setWidth(50),

            (new SelectField('show_in_qty_table', 'Pokazać w tabeli'))
                ->setOptions(['show' => 'show', 'hide' => 'hide'])
                ->setHelpText('Show this rule in the quantity table.')
                ->setWidth(50),

            (new RichText('custom_html_1', 'Custom HTML 1'))
                ->setHelpText('Optional HTML shown on the product page.')
                ->setWidth(100),
        ];
    }

    public static function getMainConditions(): array
    {
        return [
            (new AssociationUsersField('users', 'Users'))->setHelpText('Users the rule applies to. Empty = all (if no roles set).'),
            (new AssociationRolesField('roles', 'Roles'))->setHelpText('User roles the rule applies to. Empty = all (if no users set).'),
            (new AssociationProductsField('products', 'Products'))->setHelpText('Products the rule applies to. Empty = all (if no terms set).'),
            (new AssociationTermsField('woo_terms', 'Woo Terms'))->setHelpText('Product categories (terms) for this rule. Empty = all (if no products set).'),
        ];
    }

    public static function getQualifyingConditions(): array
    {
        return [
            (new AssociationRolesField('qualifying_roles', 'Qualifying Roles'))->setHelpText('Filters products from the main conditions that qualify for the rule.'),
            (new AssociationTermsField('qualifying_woo_terms', 'Qualifying Woo Terms'))->setHelpText('Filters products from the main conditions that qualify for the rule.'),
        ];
    }

    public static function getExcludingConditions(): array
    {
        return [
            (new AssociationUsersField('excluding_users', 'Excluding Users'))->setHelpText('Users excluded from this rule.'),
            (new AssociationRolesField('excluding_roles', 'Excluding Roles'))->setHelpText('Roles excluded from this rule.'),
            (new AssociationProductsField('excluding_products', 'Excluding Products'))->setHelpText('Products excluded from this rule.'),
            (new AssociationTermsField('excluding_woo_terms', 'Excluding Woo Terms'))->setHelpText('Terms excluded from this rule.'),
        ];
    }
}
