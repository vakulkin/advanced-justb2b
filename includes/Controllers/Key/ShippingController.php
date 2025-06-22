<?php

namespace JustB2b\Controllers\Key;

use WC_Product_Simple;
use WC_Shipping_Zone;
use WC_Shipping_Zones;
use JustB2b\Models\Key\ShippingMethodModel;
use JustB2b\Traits\RuntimeCacheTrait;

defined( 'ABSPATH' ) || exit;

class ShippingController extends AbstractKeyController {
	use RuntimeCacheTrait;
	private string $key = 'shipping';
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
		return ShippingMethodModel::getFieldsDefinition();
	}

	public function filterShippingMethods( $rates ) {
		$shippingMethods = $this->getShippingMethods();
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

	public static function getShippingMethods(): array {
		return self::getFromRuntimeCache( function () {
			$methods = [];

			$zones = WC_Shipping_Zones::get_zones();
			$zones[] = [ 'zone_id' => 0 ]; // "Rest of the world"

			foreach ( $zones as $zoneData ) {
				$zone = new WC_Shipping_Zone( $zoneData['zone_id'] );
				foreach ( $zone->get_shipping_methods() as $method ) {
					$methods[ $method->get_rate_id()] = new ShippingMethodModel( $method, $zone );
				}
			}

			return $methods;
		} );
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

	public static function getMethodWithMinimalFreeFrom(): ShippingMethodModel|false {
		return self::getFromRuntimeCache( function () {
			if ( ! function_exists( 'WC' ) || ! WC()->shipping ) {
				return false;
			}

			$packages = self::getShippingPackagesForFakeProduct();
			if ( empty( $packages ) ) {
				return false;
			}

			$zone = WC_Shipping_Zones::get_zone_matching_package( $packages[0] );
			if ( ! $zone ) {
				return false;
			}

			$availableRates = [];
			foreach ( $zone->get_shipping_methods( true ) as $method ) {
				if ( $method->enabled === 'yes' ) {
					$instance = clone $method;
					$instance->calculate_shipping( $packages[0] );
					$availableRates += $instance->rates;
				}
			}

			if ( empty( $availableRates ) ) {
				return false;
			}

			$shippingMethods = self::getShippingMethods();
			$smallestFreeFrom = null;
			$bestMethod = false;

			foreach ( $availableRates as $rateId => $rateObject ) {
				$method = $shippingMethods[ $rateId ] ?? null;

				if ( ! $method ) {
					continue;
				}

				if ( ! $method->isActive() ) {
					continue;
				}

				if ( $method->isEmptyFreeFrom() ) {
					continue;
				}

				$freeFrom = $method->getFreeFrom();
				if ( $smallestFreeFrom === null || $freeFrom < $smallestFreeFrom ) {
					$smallestFreeFrom = $freeFrom;
					$bestMethod = $method;
				}
			}

			return $bestMethod;
		} );
	}

	public static function getShippingPackagesForFakeProduct(): array {
		return self::getFromRuntimeCache( function () {
			$product = new WC_Product_Simple();
			$product->set_id( 0 );
			$product->set_name( 'Fake Product' );
			$product->set_price( 0.01 );
			$product->set_regular_price( 0.01 );
			$product->set_weight( '' );
			$product->set_shipping_class_id( 0 );

			$cartItem = [ 
				'key' => 'fake_product_' . uniqid(),
				'product_id' => 0,
				'variation_id' => 0,
				'variation' => [],
				'quantity' => 1,
				'data' => $product,
				'data_hash' => md5( serialize( $product ) ),
				'line_tax_data' => [ 'subtotal' => [], 'total' => [] ],
			];

			$package = [ 
				'contents' => [ $cartItem ],
				'contents_cost' => 0.01,
				'applied_coupons' => [],
				'destination' => [ 
					'country' => WC()->customer->get_shipping_country(),
					'state' => WC()->customer->get_shipping_state(),
					'postcode' => WC()->customer->get_shipping_postcode(),
					'city' => WC()->customer->get_shipping_city(),
					'address' => WC()->customer->get_shipping_address(),
					'address_2' => WC()->customer->get_shipping_address_2(),
				],
			];

			return [ $package ];
		} );
	}
}
