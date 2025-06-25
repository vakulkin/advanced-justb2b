<?php

namespace JustB2b\Models\Id;

use JustB2b\Fields\AssociationUsersField;

defined( 'ABSPATH' ) || exit;

/**
 * @feature-section admin_roles
 * @title[ru] Управление ролями клиентов
 * @desc[ru] Создание B2B-ролей и назначение их пользователям. Роли используются в правилах цен и отображении товаров.
 * @order 220
 */

/**
 * @feature admin_roles role_model
 * @title[ru] Связь пользователей с ролями
 * @desc[ru] Пользователи вручную привязываются к ролям, которые влияют на цены и товары.
 * @order 221
 */


class RoleModel extends AbstractPostModel {
	public static function getFieldsDefinition(): array {
		return [ 
			new AssociationUsersField( 'role_users', 'Users' ),
		];
	}
}
