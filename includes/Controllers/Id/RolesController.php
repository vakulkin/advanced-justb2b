<?php

namespace JustB2b\Controllers\Id;

use Carbon_Fields\Container;
use JustB2b\Fields\AbstractField;
use JustB2b\Models\Id\RoleModel;
use JustB2b\Fields\FieldBuilder;

defined( 'ABSPATH' ) || exit;

class RolesController extends AbstractCustomPostController {
	protected function __construct() {
		parent::__construct();
		$this->registerAdminColumns();
	}

	public function getSingleName(): string {
		return RoleModel::getSingleName();
	}

	public function getPluralName(): string {
		return RoleModel::getPluralName();
	}

	public function getPrefixedKey(): string {
		return RoleModel::getPrefixedKey();
	}

	public function registerCarbonFields() {
		$definitions = RoleModel::getFieldsDefinition();
		$fields = FieldBuilder::buildFields( $definitions );

		Container::make( 'post_meta', 'JustB2B' )
			->where( 'post_type', '=', RoleModel::getPrefixedKey() )
			->add_fields( $fields );
	}

	protected function registerAdminColumns(): void {
		$fields = RoleModel::getFieldsDefinition();

		$postType = RoleModel::getPrefixedKey();

		add_filter( "manage_edit-{$postType}_columns", function ($columns) use ($fields) {
			foreach ( $fields as $field ) {
				/** @var AbstractField $field */
				$columns[ $field->getKey()] = $field->getLabel();
			}
			return $columns;
		} );

		add_action( "manage_{$postType}_posts_custom_column", function ($column, $postId) use ($fields) {
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
