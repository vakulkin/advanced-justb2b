<?php

namespace JustB2b\Integrations;

use JustB2b\Fields\NonNegativeFloatField;
use JustB2b\Fields\SelectField;
use JustB2b\Traits\SingletonTrait;

defined( 'ABSPATH' ) || exit;

class WCMLIntegration {
	use SingletonTrait;

	protected function __construct() {

		add_filter( 'wcml_multi_currency_ajax_actions', [ $this, 'registerAjaxAction' ] );
		add_filter( 'justb2b_product_fields_definition', [ $this, 'getProductFieldsDefinition' ], 10, 2 );
		add_filter( 'justb2b_settings_fields_definition', [ $this, 'settingsFieldsDefinition' ], 10, 2 );
	}

	public function registerAjaxAction( array $actions ): array {
		$actions[] = 'justb2b_calculate_price';
		return $actions;
	}

	public function getProductFieldsDefinition( array $fields, array $base_keys ): array {
		$currency_codes = $this->getCurrencyCodes();
		foreach ( $currency_codes as $currency ) {
			foreach ( $base_keys as $key ) {
				$composite_key = strtolower( $currency ) . '__' . $key;
				$fields[] = (new NonNegativeFloatField( $composite_key, $composite_key ))->setWidth(25);
			}
		}
		return $fields;
	}


	public static function getCurrencyCodes(): array {
		global $woocommerce_wpml;
		if (
			isset( $woocommerce_wpml->settings['currency_options'] ) &&
			is_array( $woocommerce_wpml->settings['currency_options'] )
		) {
			return array_keys( $woocommerce_wpml->settings['currency_options'] );
		}
		return [];
	}

	public static function currencyWPMLSelectField(): SelectField {

		$default_currency = get_option( 'woocommerce_currency', 'undefined_currency' );
		$currency_codes = [ $default_currency ];
		$currency_codes = array_unique( array_merge( [ $default_currency ], static::getCurrencyCodes() ) );

		return ( new SelectField( 'rule_currency', 'Currency' ) )
			->setOptions( array_combine( $currency_codes, $currency_codes ) )
			->setHelpText( 'Currency for this rule.' )
			->setWidth( 25 );
	}

	public function settingsFieldsDefinition( array $fieldsDefinition, array $base_fields ): array {
		$currency_codes = static::getCurrencyCodes();
		foreach ( $currency_codes as $currency ) {
			$currency = strtolower( $currency );
			foreach ( $base_fields as $field ) {
				$key = "setting_{$currency}__{$field['key']}";
				$label = "{$field['label']} ({$currency})";
				$fieldsDefinition[] = ( new SelectField( $key, $label ) )
					->setOptions( [ 'net' => 'net', 'gross' => 'gross' ] )
					->setWidth( 50 );
			}
		}
		return $fieldsDefinition;
	}
}
