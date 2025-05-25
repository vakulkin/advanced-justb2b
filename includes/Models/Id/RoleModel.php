<?php

namespace JustB2b\Models\Id;

use JustB2b\Fields\AssociationUsersField;

defined('ABSPATH') || exit;

/**
 * @feature-section admin_roles
 * @title[ru] Гибкое управление ролями клиентов
 * @desc[ru] Создавайте B2B-роли (например, «оптовик», «дилер») и назначайте их пользователям. Используйте роли как условия для цен и отображения товаров.
 * @order 220
 */

/**
 * @feature admin_roles role_model
 * @title[ru] Связь пользователей с ролями
 * @desc[ru] Вы можете вручную привязывать пользователей к нужной роли — и эта роль будет использоваться при расчёте цен и отображении товаров.
 * @order 221
 */

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