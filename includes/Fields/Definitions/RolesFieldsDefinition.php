<?php

namespace JustB2b\Fields\Definitions;

use JustB2b\Fields\AssociationUsersField;

defined('ABSPATH') || exit;

class RolesFieldsDefinition
{
    public static function getMainFileds(): array
    {
        return [];
    }

    public static function getUsersFields(): array
    {
        return [
            (new AssociationUsersField('users', 'Users')),
        ];
    }
}
