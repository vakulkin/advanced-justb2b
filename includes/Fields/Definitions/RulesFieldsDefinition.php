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

            (new SelectField('show_in_table', 'Pokazac w tabeli'))
                ->setOptions([
                    'incherit' => 'incherit',
                    'dont_show' => 'dont_show',
                    'only_product' => 'only_product',
                    'only_loop' => 'only_loop',
                    'loop_and_product' => 'loop_and_product',
                ])->setWidth(50),

        ];
    }

    public static function getLogicBlocksFields(): array
    {
        return [
            (new AssociationField('logic_blocks', 'Warunki'))
                ->setTypes([
                    [
                        'type' => 'post',
                        'post_type' => Prefixer::getPrefixed('logic-block'),
                    ]
                ]),
        ];
    }
}
