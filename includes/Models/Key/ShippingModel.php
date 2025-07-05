<?php

namespace JustB2b\Models\Key;

use JustB2b\Controllers\Key\GlobalController;
use JustB2b\Models\Key\AbstractKeyModel;
use JustB2b\Models\Key\Method\ShippingMethodModel;
use JustB2b\Traits\RuntimeCacheTrait;
use WC_Product_Simple;
use WC_Shipping_Zone;
use WC_Shipping_Zones;

defined( 'ABSPATH' ) || exit;

class ShippingModel extends AbstractKeyModel {
	use RuntimeCacheTrait;

	protected function getSettingsId(): int {
		return GlobalController::getSettingsId();
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

	public static function getFieldsDefinition(): array {
		$fields = [];
		$shippingMethods = self::getShippingMethods();
		foreach ( $shippingMethods as $method ) {
			$fields = array_merge( $fields, $method->getFields() );
		}
		return $fields;
	}
}
