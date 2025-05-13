<?php

namespace JustB2b\Models;

use JustB2b\Fields\SelectField;

defined('ABSPATH') || exit;

class CartModel extends AbstractKeyModel
{
    public function getKey(): string
    {
        return 'cart';
    }

    public static function getFieldsDefinition(): array
    {
        return [
            (new SelectField('mini_cart_net_price', 'Mini cart net price visibility'))
                ->setOptions([
                    'b2x' => 'b2x',
                    'b2b' => 'b2b',
                    'b2c' => 'b2c',
                ])
                ->setHelpText(__('Choose who should see the net price in the mini cart.', 'justb2b'))
                ->setWidth(50),

            (new SelectField('mini_cart_gross_price', 'Mini cart gross price visibility'))
                ->setOptions([
                    'b2x' => 'b2x',
                    'b2b' => 'b2b',
                    'b2c' => 'b2c',
                ])
                ->setHelpText(__('Choose who should see the gross price in the mini cart.', 'justb2b'))
                ->setWidth(50),
        ];
    }
}
