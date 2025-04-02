<?php

namespace JustB2b\Fields\Definitions;


defined('ABSPATH') || exit;

use JustB2b\Fields\NumberField;

class ProductsFieldsDefinition
{
    public static function getMainFileds(): array
    {
        return [
            new NumberField('base_price_1', 'base_price_1'),
            new NumberField('base_price_2', 'base_price_2'),
            new NumberField('base_price_3', 'base_price_3'),
            new NumberField('base_price_4', 'base_price_4'),
            new NumberField('base_price_5', 'base_price_5'),
        ];
    }
}
