<?php

namespace JustB2b\Controllers\Key;

use JustB2b\Models\Key\PaymentModel;
use JustB2b\Traits\RuntimeCacheTrait;

defined( 'ABSPATH' ) || exit;

class PaymentController extends AbstractKeyController {
	use RuntimeCacheTrait;

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
		return PaymentModel::getKeyFieldsDefinition();
	}

	public function filterPaymentMethods( $available_gateways ) {
		$paymentMethods = PaymentModel::getPaymentMethods();

		// Get cart total (raw, unformatted) and optionally cache if reused
		$cartTotal = WC()->cart ? (float) WC()->cart->get_total( 'edit' ) : 0;

		foreach ( $available_gateways as $id => $gateway ) {
			if ( ! isset( $paymentMethods[ $id ] ) ) {
				continue;
			}

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

}
