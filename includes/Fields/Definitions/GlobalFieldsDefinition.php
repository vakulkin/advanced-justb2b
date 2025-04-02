<?php

namespace JustB2b\Fields\Definitions;

defined('ABSPATH') || exit;

use JustB2b\Fields\TextField;
use JustB2b\Fields\NonNegativeNumberField;
use JustB2b\Fields\SelectField;

class GlobalFieldsDefinition
{
    public static function getMainFileds(): array
    {
        return [
            (new SelectField('qty_tabel', 'qty_tabel'))
                ->setOptions([
                    'dont_show' => 'dont_show',
                    'only_product' => 'only_product',
                    'only_loop' => 'only_loop',
                    'loop_and_product' => 'loop_and_product',
                ])
                ->setWidth(50),
            (new SelectField('base_price_1', 'base_price_1'))
                ->setOptions([
                    'net' => 'net',
                    'gross' => 'gross',
                ])
                ->setWidth(50),
            (new SelectField('b2b_base_net', 'b2b_base_net'))
                ->setOptions([
                    'show' => 'show',
                    'hide' => 'hide',
                ])
                ->setWidth(50),
            (new SelectField('b2b_final_net', 'b2b_final_net'))
                ->setOptions([
                    'show' => 'show',
                    'hide' => 'hide',
                ])
                ->setWidth(50),
            (new SelectField('b2b_final_gross', 'b2b_final_gross'))
                ->setOptions([
                    'show' => 'show',
                    'hide' => 'hide',
                ])
                ->setWidth(50),
            (new SelectField('b2b_rrp_gross', 'b2b_rrp_gross'))
                ->setOptions([
                    'show' => 'show',
                    'hide' => 'hide',
                ])
                ->setWidth(50),
            (new SelectField('show_html_for_b2c', 'show_html_for_b2c'))
                ->setOptions([
                    'show' => 'show',
                    'hide' => 'hide',
                ])
                ->setWidth(50),
            (new TextField('html_for_b2c', 'html_for_b2c'))
                ->setWidth(50),
            (new SelectField('shipping', 'shipping'))
                ->setOptions([
                    'incherit' => 'on',
                    'dont_show' => 'off',
                ])->setWidth(50),
            (new NonNegativeNumberField('shipping_price', 'shipping_price'))
                ->setWidth(50),
            (new NonNegativeNumberField('free_shipping_from', 'free_shipping_from'))
                ->setWidth(50),
        ];
    }
}
