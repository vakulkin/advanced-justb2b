<?php

namespace JustB2b\Fields\Definitions;


defined('ABSPATH') || exit;

use JustB2b\Fields\NonNegativeFloatField;

class ProductsFieldsDefinition
{
    public static function getMainFileds(): array
    {
        return [
            new NonNegativeFloatField('rrp_price', 'rrp_price'),
            new NonNegativeFloatField('base_price_1', 'base_price_1'),
            new NonNegativeFloatField('base_price_2', 'base_price_2'),
            new NonNegativeFloatField('base_price_3', 'base_price_3'),
            new NonNegativeFloatField('base_price_4', 'base_price_4'),
            new NonNegativeFloatField('base_price_5', 'base_price_5'),
        ];
    }
}
