<?php

namespace JustB2b\Integrations;

use JustB2b\Traits\SingletonTrait;

defined( 'ABSPATH' ) || exit;

/**
 * @feature-section wpml_integration
 * @title[ru] Поддержка WPML и переводов
 * @title[pl] Obsługa WPML i tłumaczeń
 * @desc[ru] Расширяет проверку условий на переводы продуктов и терминов с учётом WPML.
 * @desc[pl] Rozszerza sprawdzanie warunków o uwzględnienie tłumaczeń produktów i terminów zgodnie z WPML.
 * @order 910
 */

/**
 * @feature wpml_integration product_match
 * @title[ru] Поиск товара среди переводов
 * @title[pl] Wyszukiwanie produktu wśród tłumaczeń
 * @desc[ru] Учитываются переводы товаров при проверке условий применения правил.
 * @desc[pl] Tłumaczenia produktów są uwzględniane podczas sprawdzania warunków zastosowania reguł.
 * @order 911
 */

/**
 * @feature wpml_integration term_match
 * @title[ru] Учет переводов терминов
 * @title[pl] Uwzględnianie tłumaczeń terminów
 * @desc[ru] Плагин ищет соответствие терминов в любых языковых версиях товара.
 * @desc[pl] Wtyczka wyszukuje zgodność terminów we wszystkich wersjach językowych produktu.
 * @order 912
 */


class WPMLIntegration {
	use SingletonTrait;

	protected function __construct() {
		add_filter( 'justb2b_check_product', [ $this, 'checkProduct' ], 10, 3 );
		add_filter( 'justb2b_check_terms', [ $this, 'checkTerms' ], 10, 3 );
	}

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
