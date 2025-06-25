<?php

namespace JustB2b\Models\Key;

use WC_Shipping_Method;
use WC_Shipping_Zone;
use JustB2b\Controllers\Id\UsersController;
use JustB2b\Controllers\Key\ShippingController;
use JustB2b\Fields\NonNegativeFloatField;
use JustB2b\Fields\SelectField;
use JustB2b\Fields\SeparatorField;
use JustB2b\Traits\RuntimeCacheTrait;

defined( 'ABSPATH' ) || exit;

/**
 * @feature-section shipping_ui
 * @title[ru] Отдельные методы доставки для B2B и B2C
 * @desc[ru] Настройка видимости и условий доставки по типу пользователя и сумме заказа.
 * @order 700
 */

/**
 * @feature shipping_ui method_visibility
 * @title[ru] Управление доступностью методов доставки
 * @desc[ru] Метод может быть доступен только B2B, только B2C или всем.
 * @order 701
 */

/**
 * @feature shipping_ui free_shipping_threshold
 * @title[ru] Бесплатная доставка от суммы
 * @desc[ru] Установка минимальной суммы заказа (нетто), при которой доставка становится бесплатной.
 * @order 710
 */

/**
 * @feature shipping_ui contextual_labels
 * @title[ru] Метки и зоны доставки
 * @desc[ru] Название метода включает зону и статус — администратор видит, где и как он применяется.
 * @order 720
 */


class ShippingMethodModel extends AbstractKeyModel {
	use RuntimeCacheTrait;

	protected WC_Shipping_Method $WCMethod;
	protected WC_Shipping_Zone $WCZone;

	public function __construct( WC_Shipping_Method $WCMethod, WC_Shipping_Zone $WCZone ) {
		$this->WCMethod = $WCMethod;
		$this->WCZone = $WCZone;
	}

	protected function cacheContext( array $extra = [] ): array {
		return array_merge(
			parent::cacheContext(),
			[ 'rate_id' => $this->WCMethod->get_rate_id() ]
		);
	}

	protected function getSettingsId(): int {
		return ShippingController::getSettingsId();
	}

	public function getWCMethod(): WC_Shipping_Method {
		return $this->WCMethod;
	}

	public function getWCZone(): WC_Shipping_Zone {
		return $this->WCZone;
	}

	public function getKey(): string {
		return self::getFromRuntimeCache(
			fn() => '_temp__' . str_replace( ':', '_', $this->WCMethod->get_rate_id() ),
			$this->cacheContext()
		);
	}

	public function getSepKey(): string {
		return $this->getKey() . '_sep';
	}
	public function getShowKey(): string {
		return $this->getKey() . '_show';
	}
	public function getFreeKey(): string {
		return $this->getKey() . '_free';
	}

	public function getLabel(): string {
		return self::getFromRuntimeCache( function () {
			$status = $this->WCMethod->enabled === 'yes' ? 'enabled' : 'disabled';
			return sprintf(
				'%s: %s — %s (%s)',
				$this->WCMethod->get_instance_id(),
				$this->WCZone->get_zone_name(),
				$this->WCMethod->get_title(),
				$status
			);
		}, $this->cacheContext() );
	}

	public function isActive(): bool {
		return self::getFromRuntimeCache( function () {
			$currentUser = UsersController::getCurrentUser();
			$show = $this->getFieldValue( $this->getShowKey() );

			return ! (
				( $show === 'b2b' && ! $currentUser->isB2b() ) ||
				( $show === 'b2c' && $currentUser->isB2b() )
			);
		}, $this->cacheContext() + [ 'user_id' => get_current_user_id() ] );
	}

	public function getFreeFrom(): float {
		return $this->getFieldValue( $this->getFreeKey() );
	}

	public function isEmptyFreeFrom(): bool {
		return $this->isEmptyField( $this->getFreeKey() );
	}

	public function getFields(): array {
		return self::getFromRuntimeCache( function () {
			return [ 
				new SeparatorField( $this->getSepKey(), $this->getLabel() ),
				( new SelectField( $this->getShowKey(), 'Show for users' ) )
					->setOptions( [ 'b2x' => 'b2x', 'b2c' => 'b2c', 'b2b' => 'b2b' ] )
					->setWidth( 50 ),
				( new NonNegativeFloatField( $this->getFreeKey(), 'Free from order net' ) )
					->setDefaultValue( false )
					->setWidth( 50 ),
			];
		}, $this->cacheContext() );
	}

	public static function getFieldsDefinition(): array {
		$fields = [];
		$shippingMethods = ShippingController::getShippingMethods();
		foreach ( $shippingMethods as $method ) {
			$fields = array_merge( $fields, $method->getFields() );
		}
		return $fields;
	}

	public static function getFieldsDefinition2(): array {
		return self::getFromRuntimeCache( function () {
			$boxes = [];
			foreach ( ShippingController::getShippingMethods() as $method ) {
				$boxes[] = [ 
					'fields' => $method->getFields(),
					'label' => $method->getLabel(),
					'key' => $method->getKey(),
				];
			}
			return $boxes;
		} );
	}


}
