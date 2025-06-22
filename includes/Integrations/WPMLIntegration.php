<?php

namespace JustB2b\Integrations;

use JustB2b\Traits\SingletonTrait;

defined( 'ABSPATH' ) || exit;

class WPMLIntegration {
	use SingletonTrait;

	protected function __construct() {
		// add_action( 'add_meta_boxes', [ $this, 'maybeRemoveCarbonFieldsMetaBox' ], 100 );
		add_filter( 'justb2b_check_product', [ $this, 'checkProduct' ], 10, 3 );
		add_filter( 'justb2b_check_terms', [ $this, 'checkTerms' ], 10, 3 );
	}

	// public function maybeRemoveCarbonFieldsMetaBox(): void {
	// 	$default_lang = apply_filters( 'wpml_default_language', null );
	// 	$current_lang = apply_filters( 'wpml_current_language', null );

	// 	if ( $default_lang !== $current_lang ) {
	// 		remove_meta_box( 'acf-product', 'product', 'side' );
	// 	}
	// }

	public function checkProduct( bool $result, $products, int $product_id ): bool {
		$trid = apply_filters( 'wpml_element_trid', null, $product_id, 'post_product' );
		$translations = apply_filters( 'wpml_get_element_translations', null, $trid, 'post_product' ) ?: [];

		foreach ( $translations as $translation ) {
			if ( isset( $products[ $translation->element_id ] ) ) {
				return true;
			}
		}

		return $result;
	}

	public function checkTerms( bool $result, int $product_id, array $terms ): bool {
		$productTrid = apply_filters( 'wpml_element_trid', null, $product_id, 'post_product' );
		$productTranslations = apply_filters( 'wpml_get_element_translations', null, $productTrid, 'post_product' ) ?: [];

		foreach ( $terms as $term ) {
			$termTrid = apply_filters( 'wpml_element_trid', null, $term['id'], 'tax_' . $term['taxonomy'] );
			$termTranslations = apply_filters( 'wpml_get_element_translations', null, $termTrid, 'tax_' . $term['taxonomy'] ) ?: [];

			foreach ( $termTranslations as $translatedTerm ) {
				foreach ( $productTranslations as $translation ) {
					if ( has_term( $translatedTerm->element_id, $term['taxonomy'], $translation->element_id ) ) {
						return true;
					}
				}
			}
		}

		return $result;
	}
}
