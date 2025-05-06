<?php

namespace JustB2b\Fields\Definitions;

use JustB2b\Fields\AssociationProductsField;
use JustB2b\Fields\AssociationRolesField;
use JustB2b\Fields\AssociationTermsField;
use JustB2b\Fields\RichText;
use JustB2b\Fields\TextField;
use JustB2b\Fields\SelectField;
use JustB2b\Fields\AssociationUsersField;

defined('ABSPATH') || exit;

class RulesFieldsDefinition
{
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
            (new AssociationRolesField('qualifying_roles', 'Roles')),
            (new AssociationTermsField('qualifying_woo_terms', 'Woo Terms')),
        ];
    }

    public static function getExcludingConditions(): array
    {
        return [
            (new AssociationRolesField('excluding_roles', 'Roles')),
            (new AssociationUsersField('excluding_users', 'Users')),
            (new AssociationProductsField('excluding_products', 'Products')),
            (new AssociationTermsField('excluding_woo_terms', 'Woo Terms')),
        ];
    }
}
