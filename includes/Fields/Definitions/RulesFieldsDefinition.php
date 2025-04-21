<?php

namespace JustB2b\Fields\Definitions;


defined('ABSPATH') || exit;

use JustB2b\Utils\Prefixer;
use JustB2b\Fields\SelectField;
use JustB2b\Fields\TextField;
use JustB2b\Fields\AssociationField;

class RulesFieldsDefinition
{
    protected static array $startPrices = [
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

    public static function getMainFileds(): array
    {
        return [
            (new TextField('priority', 'Priority'))
                ->setAttribute('type', 'number')
                ->setAttribute('step', 'any')
                ->setWidth(50),

            (new SelectField('kind', 'Rodzaj'))
                ->setOptions([
                    'start_price' => 'start_price',
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
                    'request_price' => 'request_price',
                    'hide_product' => 'hide_product',
                    'non_purchasable' => 'non_purchasable',
                ])
                ->setWidth(50),

            (new SelectField('start_price', 'Start price'))
                ->setOptions(self::$startPrices)
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

            (new SelectField('show_in_qty_table', 'Pokazac w tabeli'))
                ->setOptions([
                    'show' => 'show',
                    'hide' => 'hide',
                ])->setWidth(50),

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
            (new AssociationField('products', 'Products'))
                ->setTypes([
                    [
                        'type' => 'post',
                        'post_type' => 'product',
                    ],
                    [
                        'type' => 'post',
                        'post_type' => 'product_variation',
                    ]
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

    // public static function getLogicBlocksFields(): array
    // {
    //     return [
    //         (new AssociationField('logic_blocks', 'Warunki'))
    //             ->setTypes([
    //                 [
    //                     'type' => 'post',
    //                     'post_type' => Prefixer::getPrefixed('logic-block'),
    //                 ]
    //             ]),
    //     ];
    // }
}
