<?php

namespace JustB2b\Models;

use JustB2b\Fields\AssociationUsersField;

defined('ABSPATH') || exit;

class RoleModel extends AbstractPostModel
{
    protected static string $key = 'role';

    public static function getSingleName(): string
    {
        return __('Role', 'justb2b');
    }
    public static function getPluralName(): string
    {
        return __('Roles', 'justb2b');
    }

    public static function getFieldsDefinition(): array
    {
        return [
            new AssociationUsersField('users', 'Users'),
        ];
    }
}