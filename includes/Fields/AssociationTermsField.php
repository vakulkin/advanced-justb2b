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
						'key' => $term->name,
						'type' => 'term',
						'valid' => true,
					];
				} else {
					$result[ $termId ] = [ 
						'key' => "removed taxomony {$termId}",
						'type' => 'error',
						'valid' => false,
					];
				}
			}
		}
		return $result;
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
