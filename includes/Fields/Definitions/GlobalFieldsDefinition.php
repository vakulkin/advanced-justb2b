<?php

namespace JustB2b\Fields\Definitions;

defined('ABSPATH') || exit;

use JustB2b\Fields\TextField;
use JustB2b\Fields\NonNegativeFloatField;
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
            (new SelectField('b2b_rule_base', 'b2b_rule_base'))
                ->setOptions([
                    'net' => 'net',
                    'gross' => 'gross',
                ])
                ->setWidth(50),
            (new SelectField('b2c_rule_base', 'b2c_rule_base'))
                ->setOptions([
                    'net' => 'net',
                    'gross' => 'gross',
                ])
                ->setWidth(50),
            (new SelectField('rrp_price', 'rrp_price'))
                ->setOptions([
                    'net' => 'net',
                    'gross' => 'gross',
                ])
                ->setWidth(50),
            (new SelectField('base_price_1', 'base_price_1'))
                ->setOptions([
                    'net' => 'net',
                    'gross' => 'gross',
                ])
                ->setWidth(50),
            (new SelectField('base_price_2', 'base_price_2'))
                ->setOptions([
                    'net' => 'net',
                    'gross' => 'gross',
                ])
                ->setWidth(50),
            (new SelectField('base_price_3', 'base_price_3'))
                ->setOptions([
                    'net' => 'net',
                    'gross' => 'gross',
                ])
                ->setWidth(50),
            (new SelectField('base_price_4', 'base_price_4'))
                ->setOptions([
                    'net' => 'net',
                    'gross' => 'gross',
                ])
                ->setWidth(50),
            (new SelectField('base_price_5', 'base_price_5'))
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
            (new SelectField('b2b_base_gross', 'b2b_base_gross'))
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
            (new SelectField('b2b_rrp_net', 'b2b_rrp_net'))
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
            (new SelectField('show_b2c_html', 'show_b2c_html'))
                ->setOptions([
                    'show' => 'show',
                    'hide' => 'hide',
                ])
                ->setWidth(50),
            (new TextField('b2c_html', 'b2c_html'))
                ->setWidth(50),
            (new SelectField('b2b_shipping', 'b2b_shipping'))
                ->setOptions([
                    'incherit' => 'on',
                    'dont_show' => 'off',
                ])->setWidth(50),
            (new NonNegativeFloatField('b2b_shipping_price', 'b2b_shipping_price'))
                ->setWidth(50),
            (new NonNegativeFloatField('b2b_shipping_free_from', 'b2b_shipping_free_from'))
                ->setWidth(50),
        ];
    }
}
