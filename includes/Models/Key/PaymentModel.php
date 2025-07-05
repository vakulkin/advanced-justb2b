<?php

namespace JustB2b\Models\Key;

use JustB2b\Controllers\Key\GlobalController;
use JustB2b\Models\Key\AbstractKeyModel;
use JustB2b\Models\Key\Method\PaymentMethodModel;
use WC_Payment_Gateways;

defined( 'ABSPATH' ) || exit;


class PaymentModel extends AbstractKeyModel {

	protected function getSettingsId(): int {
		return GlobalController::getSettingsId();
	}

	public static function getPaymentMethods(): array {
		return self::getFromRuntimeCache( function () {
			$methods = [];
			$gateways = WC_Payment_Gateways::instance()->payment_gateways();
			foreach ( $gateways as $gateway ) {
				$methods[ $gateway->id ] = new PaymentMethodModel( $gateway );
			}
			return $methods;
		} );
	}

	public static function getFieldsDefinition(): array {
		return self::getFromRuntimeCache( function () {
			$fields = [];
			foreach ( self::getPaymentMethods() as $method ) {
				$fields = array_merge( $fields, $method->getFields() );
			}
			return $fields;
		} );
	}

}
