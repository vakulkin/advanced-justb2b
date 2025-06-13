<?php

namespace JustB2b\Fields;

defined( 'ABSPATH' ) || exit;

use Carbon_Fields\Field\Field;

abstract class AssociationField extends AbstractField {
	protected string $type = 'association';
	protected array $postTypes = [];

	public function __construct( string $key, string $label ) {
		parent::__construct( $key, $label );
		$this->defaultValue = [];
	}

	public function setPostTypes( array $postTypes ): static {
		$this->postTypes = $postTypes;
		return $this;
	}

	public function toCarbonField(): Field {
		/** @var Field $field */
		$field = parent::toCarbonField();
		$field->set_types( $this->postTypes );
		return $field;
	}

	protected function isEmpty( $value ): bool {
		return parent::isEmpty( $value ) || is_array( $value ) && count( $value ) === 0;
	}

	public function getPostFieldOriginValue( int $postId ): array {
		global $wpdb;
		error_log( $postId . ' ' . $this->prefixedKey . ' ' . print_r( $this->getOriginValuesFromMetaTable( $postId, $wpdb->postmeta, 'post_id', '|id' ), true ) );
		return self::getFromRuntimeCache(
			fn() => $this->getOriginValuesFromMetaTable( $postId, $wpdb->postmeta, 'post_id', '|id' ),
			[ 'post_id' => $postId, 'key' => $this->prefixedKey ]
		);
	}

	public function getUserFieldOriginValue( int $userId ): array {
		global $wpdb;
		return self::getFromRuntimeCache(
			fn() => $this->getOriginValuesFromMetaTable( $userId, $wpdb->usermeta, 'user_id', '|id' ),
			[ 'user_id' => $userId, 'key' => $this->prefixedKey ]
		);
	}

	public function getOptionOriginValue(): array {
		return self::getFromRuntimeCache(
			fn() => $this->getOriginValuesFromOptionsTable( '|id' ),
			[ 'key' => $this->prefixedKey ]
		);
	}


	protected function renderEntities(
		array $values,
		callable $resolver,
		callable $linkGenerator,
		callable $labelGetter
	): string {
		$visibleCount = 3;

		return print_r( $values, true );

		// $resolvedEntities = $this->resolveEntities( $values, $resolver );
		// $renderedLinks = $this->renderVisibleEntities( $resolvedEntities, $linkGenerator, $labelGetter, $visibleCount );
		// $moreIndicator = $this->renderRemainingCountIndicator( count( $resolvedEntities ), $visibleCount );

		// return sprintf(
		// 	'<div class="justb2b-associations">%s%s</div>',
		// 	$renderedLinks,
		// 	$moreIndicator
		// );
	}

	protected function resolveEntities( array $values, callable $resolver ): array {
		$entities = [];

		foreach ( $values as $value ) {
			$id = (int) ( $value['id'] ?? 0 );
			if ( ! $id ) {
				continue;
			}

			$entity = $resolver( $id );
			if ( $entity && ! is_wp_error( $entity ) ) {
				$subtype = $value['subtype'] ?? $value['taxonomy'] ?? ( $value['user_email'] ?? false ? 'user' : 'item' );
				$entities[] = [ 'entity' => $entity, 'subtype' => $subtype ];
			}
		}

		return $entities;
	}

	protected function renderVisibleEntities(
		array $entities,
		callable $linkGenerator,
		callable $labelGetter,
		int $visibleCount
	): string {
		$output = '';

		foreach ( array_slice( $entities, 0, $visibleCount ) as $item ) {
			$label = esc_attr( $labelGetter( $item['entity'] ) );
			$url = esc_url( $linkGenerator( $item['entity'] ) );
			$output .= sprintf(
				'<a class="justb2b-association-field justb2b-%s-field-value" href="%s" target="_blank" rel="noopener noreferrer" title="%s">%s</a>',
				$item['subtype'],
				$url,
				$label,
				$label
			);
		}

		return $output;
	}

	protected function renderRemainingCountIndicator( int $total, int $visibleCount ): string {
		$remaining = $total - $visibleCount;

		if ( $remaining > 0 ) {
			// Use a generic "item" subtype; you can adjust this logic if needed.
			return sprintf(
				'<span class="justb2b-association-field justb2b-item-field-value">+%s</span>',
				esc_html( $remaining )
			);
		}

		return '';
	}

}
