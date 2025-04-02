<?php

namespace JustB2b\Fields\Definitions;


defined('ABSPATH') || exit;

use JustB2b\Fields\SelectField;
use JustB2b\Fields\AssociationField;

class RolesFieldsDefinition {
    public static function getMainFileds(): array {
        return [
            (new SelectField('kind', 'Rodzaj'))
                ->setOptions([
                    'b2b' => 'b2b',
                    'b2c' => 'b2c',
                ])
                ->setWidth(50)
        ];
    }

    public static function getUsersFields(): array
    {
        return [
            (new AssociationField('users', 'Users'))
                ->setTypes([
                    [
                        'type' => 'user',
                    ]
                ]),
        ];
    }
}
