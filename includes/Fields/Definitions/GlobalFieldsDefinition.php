<?php

namespace JustB2b\Fields\Definitions;

use JustB2b\Fields\RichText;
use JustB2b\Fields\SeparatorField;

defined('ABSPATH') || exit;

use JustB2b\Fields\TextField;
use JustB2b\Fields\SelectField;

class GlobalFieldsDefinition
{
    public static function getBaseFields(): array
    {
        $fieldsData = [
            ['key' => 'rrp_price', 'label' => 'RRP Prce'],
            ['key' => 'base_price_1', 'label' => 'Base price 1'],
            ['key' => 'base_price_2', 'label' => 'Base price 2'],
            ['key' => 'base_price_3', 'label' => 'Base price 3'],
            ['key' => 'base_price_4', 'label' => 'Base price 4'],
            ['key' => 'base_price_5', 'label' => 'Base price 5'],
        ];
        $filedsDefinition = [];
        foreach ($fieldsData as $field) {
            $key = $field['key'];
            $label = $field['label'];

            $filedsDefinition[] = (new SelectField($key, $label))
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
            'hide' => 'hide',
            'only_product' => 'only_product',
            'only_loop' => 'only_loop',
        ];

        $filedsDefinition = [];
        foreach ($fieldsData as $field) {
            $key = $field['key'];

            $sepKey = "sep_{$key}";
            $filedsDefinition[] = (new SeparatorField($sepKey, $field['label']));

            foreach (['single', 'loop'] as $place) {
                foreach (['prefix', 'postfix'] as $position) {
                    $prefix = "{$position}_{$key}_{$place}";
                    $filedsDefinition[] = (new TextField($prefix, "Prefix {$place}"))->setWidth(width: 25);
                }
            }

            foreach (['b2c', 'b2b'] as $type) {
                $typeKey = "{$type}_{$key}";
                $filedsDefinition[] = (new SelectField($typeKey, "Show for {$type} users"))
                    ->setOptions($showOptions)
                    ->setWidth(50);
            }
        }

        return $filedsDefinition;
    }

    public static function getB2cFileds(): array
    {
        $fieldsDefinition = [];
        $types = ['b2c', 'b2b'];

        foreach ($types as $type) {
            $fieldsDefinition[] = (new SelectField("show_{$type}_html_1", "show_{$type}_html_1"))
                ->setOptions([
                    'show' => 'show',
                    'hide' => 'hide',
                ])
                ->setWidth(100);
            $fieldsDefinition[] = (new RichText("{$type}_html_1", "{$type}_html_1"))
                ->setWidth(100);
        }

        return $fieldsDefinition;
    }

}
