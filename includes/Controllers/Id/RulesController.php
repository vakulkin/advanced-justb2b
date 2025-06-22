<?php

namespace JustB2b\Controllers\Id;

use JustB2b\Fields\AbstractField;
use JustB2b\Models\Id\RuleModel;

defined( 'ABSPATH' ) || exit;

/**
 * @feature-section admin_rules
 * @title[ru] Удобное управление ценовыми правилами
 * @desc[ru] Вы можете легко создавать и редактировать правила, которые автоматически изменяют цену в зависимости от роли клиента, количества, категории и других условий — без необходимости в коде.
 * @order 200
 */

/**
 * @feature admin_rules controller
 * @title[ru] Всё под контролем — в одном месте
 * @desc[ru] Все ценовые правила собраны в удобной таблице в админке: редактируйте, фильтруйте, сортируйте.
 * @order 201
 */

class RulesController extends AbstractCustomPostController {
	protected function __construct() {
		parent::__construct();
		$this->registerAdminColumns();
		add_filter( 'acf/fields/relationship/result', [ $this, 'addImageRelation' ], 10, 4 );
	}

	public function addImageRelation( $title, $post, $field, $post_id ) {
		// if ( isset( $field['name'] ) && $field['name'] === 'your_relationship_field_name' ) {
		$image_size = [ 22, 22 ];
		$thumbnail = get_the_post_thumbnail( $post->ID, $image_size );
		if ( $thumbnail ) {
			$title = '<div class="thumbnail">' . $thumbnail . '</div>' . $title;
		}
		return $title;
	}

	public static function getKey() {
		return 'rule';
	}

	public static function getSingleName(): string {
		return 'Rule';
	}

	public static function getPluralName(): string {
		return 'Rules';
	}
	public function getDefinitions(): array {
		return RuleModel::getFieldsDefinition();
	}

	protected function registerAdminColumns(): void {
		$fields = RuleModel::getFieldsDefinition();

		$postType = self::getPrefixedKey();

		add_filter( "manage_edit-{$postType}_columns",
			function ($columns) use ($fields) {
				/** @var AbstractField $field */
				foreach ( $fields as $field ) {
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

		// add_filter( "manage_edit-{$postType}_sortable_columns", function ($columns) use ($fields) {
		// 	/** @var AbstractField $field */
		// 	foreach ( $fields as $field ) {
		// 		if ( $field->getAttribute( 'type' ) === 'number' ) {
		// 			$columns[ $field->getKey()] = $field->getPrefixedKey();
		// 		}
		// 	}
		// 	return $columns;
		// } );
	}

}
