<?php

namespace JustB2b\Fields\Definitions;

use JustB2b\Fields\SeparatorField;

defined('ABSPATH') || exit;

use JustB2b\Fields\TextField;
use JustB2b\Fields\SelectField;

class GlobalFieldsDefinition
{
    public static function getBaseFields(): array
    {
        $fieldsData = [
            ['key' => 'rrp_price', 'label' => 'rrp_price'],
            ['key' => 'base_price_1', 'label' => 'base_price_1'],
            ['key' => 'base_price_2', 'label' => 'base_price_2'],
            ['key' => 'base_price_3', 'label' => 'base_price_3'],
            ['key' => 'base_price_4', 'label' => 'base_price_4'],
            ['key' => 'base_price_5', 'label' => 'base_price_5'],
        ];
        $filedsDefinition = [];
        foreach ($fieldsData as $field) {
            $filedsDefinition[] = (new SelectField($field['key'], $field['label']))
                ->setOptions([
                    'net' => 'net',
                    'gross' => 'gross',
                ])
                ->setWidth(50);
        }
        return $filedsDefinition;
    }

    public static function getMainFileds(): array
    {
        $fieldsData = [
            ['key' => 'base_net', 'label' => 'Base Net'],
            ['key' => 'base_gross', 'label' => 'Base Gross'],
            ['key' => 'final_net', 'label' => 'Final Net'],
            ['key' => 'final_gross', 'label' => 'Final Gross'],
            ['key' => 'rrp_net', 'label' => 'RRP Net'],
            ['key' => 'rrp_gross', 'label' => 'RRP Gross'],
            ['key' => 'qty_table', 'label' => 'Qty Table'],
        ];

        $showOptions = [
            'show' => 'show',
            'dont_show' => 'hide',
            'only_product' => 'only_product',
            'only_loop' => 'only_loop',
        ];

        $filedsDefinition = [];
        foreach ($fieldsData as $field) {
            $key = $field['key'];

            $sepKey = "sep_{$key}";
            $filedsDefinition[] = (new SeparatorField($sepKey, $field['label']));
            foreach (['b2c', 'b2b'] as $type) {
                $typeKey = "{$type}_{$key}";
                $filedsDefinition[] = (new SelectField($typeKey, $type))
                    ->setOptions($showOptions)
                    ->setWidth(25);
            }
            $prefixKey = "prefix_{$key}";
            $filedsDefinition[] = (new TextField($prefixKey, 'Prefix'))->setWidth(width: 25);
            $postfixKey = "postfix_{$key}";
            $filedsDefinition[] = (new TextField($postfixKey, 'Postfix'))->setWidth(25);
        }
        
        return $filedsDefinition;
    }

    public static function getB2cFileds(): array
    {
        return [
            (new SelectField('show_b2c_html', 'show_b2c_html'))
                ->setOptions([
                    'show' => 'show',
                    'hide' => 'hide',
                ])
                ->setWidth(50),
            (new TextField('b2c_html', 'b2c_html'))
                ->setWidth(50),
        ];
    }

}
