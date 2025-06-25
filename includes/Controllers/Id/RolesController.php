<?php

namespace JustB2b\Controllers\Id;

use JustB2b\Models\Id\RoleModel;

defined( 'ABSPATH' ) || exit;

class RolesController extends AbstractCustomPostController {
	protected function __construct() {
		parent::__construct();
		$this->registerAdminColumns();
	}

	public static function getKey() {
		return 'role';
	}

	public static function getSingleName(): string {
		return 'Role';
	}

	public static function getPluralName(): string {
		return 'Roles';
	}

	public function getDefinitions(): array {
		return RoleModel::getKeyFieldsDefinition();
	}
}
