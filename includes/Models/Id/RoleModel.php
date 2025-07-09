<?php

namespace JustB2b\Models\Id;

use JustB2b\Fields\AssociationUsersField;

defined( 'ABSPATH' ) || exit;

/**
 * @feature-section admin_roles
 * @title[ru] Управление ролями клиентов
 * @title[pl] Zarządzanie rolami klientów
 * @desc[ru] Создание B2B-ролей и назначение их пользователям. Роли используются в правилах цен и отображении товаров.
 * @desc[pl] Tworzenie ról B2B i przypisywanie ich użytkownikom. Role są wykorzystywane w regułach cenowych i wyświetlaniu produktów.
 * @order 220
 */

/**
 * @feature admin_roles role_model
 * @title[ru] Связь пользователей с ролями
 * @title[pl] Powiązanie użytkowników z rolami
 * @desc[ru] Пользователи вручную привязываются к ролям, которые влияют на цены и товары.
 * @desc[pl] Użytkownicy są ręcznie przypisywani do ról, które wpływają na ceny i widoczność produktów.
 * @order 221
 */



class RoleModel extends AbstractPostModel {
	public static function getFieldsDefinition(): array {
		return [ 
			new AssociationUsersField( 'role_users', 'Users' ),
		];
	}
}
