<?php

namespace JustB2b\Fields\Definitions;


defined('ABSPATH') || exit;

use JustB2b\Utils\Prefixer;
use JustB2b\Fields\AssociationField;

class LogicBlocksFieldsDefinition
{
    public static function getMainFileds(): array
    {
        return [];
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
}
