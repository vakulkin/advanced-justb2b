<?php

namespace JustB2b\Fields;

defined( 'ABSPATH' ) || exit;

class AssociationTermsField extends AbstractOptionsField {

	public function __construct( string $key, string $label ) {
		parent::__construct( $key, $label );
		$this->setOptions( $this->getCombinedProductTerms() );
		$this->defaultValue = [];
	}

	public function toACF(): array {
		$field = parent::toACF();
		$field['type'] = 'checkbox';
		$field['multiple'] = 1;
		return $field;
	}

	public function getValue( int $id ): array {
		$terms = parent::getValue( $id );
		$result = [];
		if ( is_array( $terms ) ) {
			foreach ( $terms as $termId ) {
				if ( $termId && ( $term = get_term( $termId ) ) && ! is_wp_error( $term ) ) {
					$result[ $term->term_id ] = [ 
						'key' => $term->taxonomy,
						'valid' => true,
					];
				} else {
					$result[ $term->term_id ] = [ 
						'key' => "removed taxomony {$term->term_id}",
						'valid' => false,
					];
				}
			}
		}
		return $result;
	}

	public function renderValue( int $parentId ): string {
		return $this->renderEntities(
			$this->getValue( $parentId ),
			fn( $id ) => get_term( $id ),
			fn( $term ) => get_term_link( $term ),
			fn( $term ) => $term->name
		);
	}

	public function getCombinedProductTerms(): array {
		global $wpdb;

		$results = $wpdb->get_results( "SELECT 
                t.term_id,
                t.name,
                tt.taxonomy
            FROM {$wpdb->prefix}terms t
            INNER JOIN {$wpdb->prefix}term_taxonomy tt ON t.term_id = tt.term_id
            WHERE tt.taxonomy = 'product_cat'
            OR tt.taxonomy = 'product_tag'
            OR tt.taxonomy LIKE 'pa_%'" );

		$choices = [];

		foreach ( $results as $row ) {
			$label = $row->name . ' (' . $row->taxonomy . ')';
			$choices[ $row->term_id ] = $label;
		}

		return $choices;
	}


}
