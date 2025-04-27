<?php

namespace JustB2b\Fields\Definitions;


defined('ABSPATH') || exit;

use JustB2b\Fields\AssociationField;

class RolesFieldsDefinition
{
    public static function getMainFileds(): array
    {
        return [];
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
