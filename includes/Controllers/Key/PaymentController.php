<?php

namespace JustB2b\Controllers\Key;

use WC_Payment_Gateways;
use JustB2b\Models\Key\PaymentMethodModel;
use JustB2b\Traits\RuntimeCacheTrait;

defined( 'ABSPATH' ) || exit;



class PaymentController extends AbstractKeyController {
	use RuntimeCacheTrait;

	private string $key = 'payment';
	protected function __construct() {
		parent::__construct();
		add_filter( 'woocommerce_available_payment_gateways', [ $this, 'filterPaymentMethods' ] );
	}

	public static function getKey() {
		return 'payment';
	}

	public static function getSingleName(): string {
		return 'Payment';
	}

	public static function getPluralName(): string {
		return 'Payments';
	}

	public function getDefinitions(): array {
		return PaymentMethodModel::getFieldsDefinition();
	}

	public function filterPaymentMethods( $available_gateways ) {
		$paymentMethods = self::getPaymentMethods();

		// Get cart total (raw, unformatted) and optionally cache if reused
		$cartTotal = WC()->cart ? (float) WC()->cart->get_total( 'edit' ) : 0;

		foreach ( $available_gateways as $id => $gateway ) {
			if ( ! isset( $paymentMethods[ $id ] ) ) {
				continue;
			}

			/** @var PaymentMethodModel $method */
			$method = $paymentMethods[ $id ];

			if ( ! $method->isActive() ) {
				unset( $available_gateways[ $id ] );
				continue;
			}

			if (
				( $cartTotal < $method->getMinOrderTotal() ) ||
				( ! $method->isEmptyMaxOrderTotal() && $cartTotal > $method->getMaxOrderTotal() )
			) {
				unset( $available_gateways[ $id ] );
			}
		}

		return $available_gateways;
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

}
