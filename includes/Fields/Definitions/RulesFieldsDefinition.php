<?php

namespace JustB2b\Fields\Definitions;

use JustB2b\Fields\RichText;

defined('ABSPATH') || exit;

use JustB2b\Utils\Prefixer;
use JustB2b\Fields\SelectField;
use JustB2b\Fields\TextField;
use JustB2b\Fields\AssociationField;

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
            (new AssociationField('roles', 'Roles'))
                ->setTypes([
                    [
                        'type' => 'post',
                        'post_type' => Prefixer::getPrefixed('role'),
                    ]
                ]),
            (new AssociationField('users', 'Users'))
                ->setTypes([
                    [
                        'type' => 'user',
                    ]
                ]),
            (new AssociationField('products', 'Products'))
                ->setTypes([
                    [
                        'type' => 'post',
                        'post_type' => 'product',
                    ],
                    // [
                    //     'type' => 'post',
                    //     'post_type' => 'product_variation',
                    // ]
                ]),

            (new AssociationField('woo_terms', 'Woo Terms'))
                ->setTypes([
                    [
                        'type' => 'term',
                        'taxonomy' => 'product_cat',
                    ],
                    [
                        'type' => 'term',
                        'taxonomy' => 'product_tag',
                    ]
                ]),
        ];
    }

    public static function getQualifyingConditions(): array
    {
        return [
            (new AssociationField('qualifying_roles', 'Roles'))
                ->setTypes([
                    [
                        'type' => 'post',
                        'post_type' => Prefixer::getPrefixed('role'),
                    ]
                ]),

            (new AssociationField('qualifying_woo_terms', 'Woo Terms'))
                ->setTypes([
                    [
                        'type' => 'term',
                        'taxonomy' => 'product_cat',
                    ],
                    [
                        'type' => 'term',
                        'taxonomy' => 'product_tag',
                    ]
                ]),
        ];
    }

    public static function getExcludingConditions(): array
    {
        return [
            (new AssociationField('excluding_roles', 'Roles'))
                ->setTypes([
                    [
                        'type' => 'post',
                        'post_type' => Prefixer::getPrefixed('role'),
                    ]
                ]),
            (new AssociationField('excluding_users', 'Users'))
                ->setTypes([
                    [
                        'type' => 'user',
                    ]
                ]),
            (new AssociationField('excluding_products', 'Products'))
                ->setTypes([
                    [
                        'type' => 'post',
                        'post_type' => 'product',
                    ],
                    // [
                    //     'type' => 'post',
                    //     'post_type' => 'product_variation',
                    // ]
                ]),

            (new AssociationField('excluding_woo_terms', 'Woo Terms'))
                ->setTypes([
                    [
                        'type' => 'term',
                        'taxonomy' => 'product_cat',
                    ],
                    [
                        'type' => 'term',
                        'taxonomy' => 'product_tag',
                    ]
                ]),
        ];
    }
}
