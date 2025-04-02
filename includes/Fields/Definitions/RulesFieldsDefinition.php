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
        'price' => 'price',
        'regular_price' => 'regular_price',
        'sale_price' => 'sale_price',
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
                    'minus_percent' => 'minus_percent',
                    'plus_percent' => 'plus_percent',
                    'minus_number' => 'minus_number',
                    'plus_number' => 'plus_number',
                    'equals_number' => 'equals_number',
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
