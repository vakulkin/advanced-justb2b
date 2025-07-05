<?php

namespace JustB2b\Controllers\Key;

use JustB2b\Models\Key\Method\ShippingMethodModel;
use JustB2b\Models\Key\ShippingModel;
use JustB2b\Traits\RuntimeCacheTrait;

defined( 'ABSPATH' ) || exit;

class ShippingController extends AbstractKeyController {
	use RuntimeCacheTrait;
	
	protected function __construct() {
		parent::__construct();
		add_filter( 'woocommerce_package_rates', [ $this, 'filterShippingMethods' ] );
		add_action( 'woocommerce_checkout_update_order_review', [ $this, 'resetShippingCache' ] );
	}

	public static function getKey() {
		return 'shipping';
	}

	public static function getSingleName(): string {
		return 'Shipping';
	}

	public static function getPluralName(): string {
		return 'Shippings';
	}

	public function getDefinitions(): array {
		return ShippingMethodModel::getKeyFieldsDefinition();
	}

	public function filterShippingMethods( $rates ) {
		$shippingMethods = ShippingModel::getShippingMethods();
		foreach ( $rates as $rate_id => $rate ) {
			if ( isset( $shippingMethods[ $rate_id ] ) ) {
				$method = $shippingMethods[ $rate_id ];

				if ( ! $method->isActive() ) {
					unset( $rates[ $rate_id ] );
					continue;
				}

				if ( ! $method->isEmptyFreeFrom() ) {
					$cartSubTotal = WC()->cart->get_subtotal();
					if ( $cartSubTotal > 0 && $cartSubTotal >= $method->getFreeFrom() ) {
						$rates[ $rate_id ]->cost = 0.0;
					}
				}
			}
		}
		return $rates;
	}

	public function resetShippingCache() {
		if ( ! function_exists( 'WC' ) || ! WC()->cart || ! WC()->session ) {
			return;
		}

		$packages = WC()->cart->get_shipping_packages();
		foreach ( $packages as $key => $value ) {
			$sessionKey = "shipping_for_package_$key";
			WC()->session->set( $sessionKey, null );
		}
	}

}
