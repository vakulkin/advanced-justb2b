<?php

namespace JustB2b\Controllers\Id;

use JustB2b\Fields\AbstractField;
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
		return RoleModel::getFieldsDefinition();
	}

	protected function registerAdminColumns(): void {
		$fields = RoleModel::getFieldsDefinition();

		$postType = self::getPrefixedKey();

		add_filter( "manage_edit-{$postType}_columns",
			function ($columns) use ($fields) {
				foreach ( $fields as $field ) {
					/** @var AbstractField $field */
					$columns[ $field->getKey()] = $field->getLabel();
				}
				return $columns;
			} );

		add_action( "manage_{$postType}_posts_custom_column",
			function ($column, $postId) use ($fields) {
				foreach ( $fields as $field ) {
					/** @var AbstractField $field */
					if ( $column === $field->getKey() ) {
						echo $field->renderValue( $postId );
						return;
					}
				}
			}, 10, 2 );
	}
}
