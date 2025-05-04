<?php

namespace JustB2b\Models;

defined('ABSPATH') || exit;

class RoleModel extends BasePostModel
{
    protected static string $key = 'role';

    public static function getSingleName(): string {
        return __('Role', 'justb2b');
    }
    public static function getPluralName(): string {
        return __('Roles', 'justb2b');
    }

    
}