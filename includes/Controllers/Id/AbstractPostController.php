<?php

namespace JustB2b\Controllers\Id;

use JustB2b\Fields\FieldBuilder;
use JustB2b\Fields\AbstractField;
use JustB2b\Traits\SingletonTrait;

defined( 'ABSPATH' ) || exit;


abstract class AbstractPostController extends AbstractIdController {
	use SingletonTrait;

	protected function __construct() {
		parent::__construct();
	}

	public function registerACF(): void {
		$default_lang = apply_filters( 'wpml_default_language', null );
		$current_lang = apply_filters( 'wpml_current_language', null );

		if ( $default_lang !== $current_lang ) {
			return;
		}

		if ( ! function_exists( 'acf_add_local_field_group' ) ) {
			return;
		}

		$fields = FieldBuilder::buildACF( $this->getDefinitions() );

		if ( ! empty( $fields ) ) {
			$params = [ 
				'key' => static::getKey(),
				'title' => static::getKey(),
				'fields' => $fields,
				'location' => [ 
					[ 
						[ 
							'param' => 'post_type',
							'operator' => '==',
							'value' => static::getPrefixedKey(),
						],
					],
				],
			];

			acf_add_local_field_group( $params );
		}
	}

	protected function registerAdminColumns(): void {
		$fields = $this->getDefinitions();
		$postType = self::getPrefixedKey();

		add_filter( "manage_edit-{$postType}_columns", function ($columns) use ($fields) {
			foreach ( $fields as $field ) {
				/** @var AbstractField $field */
				$columns[ $field->getKey()] = sprintf(
					'<span class="justb2b-column-index" title="%s">%s</span>',
					esc_attr( $field->getLabel() ),
					esc_attr( $field->getIndex() )
				);
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

		add_filter( "manage_edit-{$postType}_sortable_columns", function ($columns) use ($fields) {
			/** @var AbstractField $field */
			foreach ( $fields as $field ) {
				if ( $this->isInstanceOrChildOf( 'JustB2b\Fields\NumberField', $field ) ) {
					$columns[ $field->getKey()] = $field->getPrefixedKey();
				}
			}
			return $columns;
		} );
	}

	protected function isInstanceOrChildOf( string $class, object $object ): bool {
		return get_class( $object ) === $class || is_subclass_of( $object, $class );
	}
}

