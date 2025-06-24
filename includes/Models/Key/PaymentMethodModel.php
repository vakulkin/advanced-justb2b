<?php

namespace JustB2b\Models\Key;

use JustB2b\Controllers\Key\PaymentController;
use WC_Payment_Gateway;
use JustB2b\Controllers\Id\UsersController;
use JustB2b\Fields\SelectField;
use JustB2b\Fields\SeparatorField;
use JustB2b\Fields\NonNegativeFloatField;
use JustB2b\Traits\RuntimeCacheTrait;

defined( 'ABSPATH' ) || exit;

/**
 * @feature-section payment_rules
 * @title[ru] Настройка платёжных методов
 * @desc[ru] Управляйте доступностью способов оплаты в зависимости от типа клиента и суммы заказа — это повышает контроль, безопасность и UX.
 * @order 700
 */

/**
 * @feature payment_rules method_visibility
 * @title[ru] Отображение методов оплаты по ролям и типу клиента
 * @desc[ru] Показывайте или скрывайте способы оплаты для B2B и B2C клиентов в зависимости от бизнес-логики и условий.
 * @order 701
 */


class PaymentMethodModel extends AbstractKeyModel {
	use RuntimeCacheTrait;

	protected WC_Payment_Gateway $WCMethod;

	public function __construct( WC_Payment_Gateway $WCMethod ) {
		$this->WCMethod = $WCMethod;
	}

	protected function cacheContext( array $extra = [] ): array {
		return array_merge( [ 
			parent::cacheContext(),
			'method_id' => $this->WCMethod->id
		] );
	}

	protected function getSettingsId(): int {
		return PaymentController::getSettingsId();
	}

	public function getWCMethod(): WC_Payment_Gateway {
		return $this->WCMethod;
	}

	public function getKey(): string {
		return self::getFromRuntimeCache(
			fn() => '_temp__payment' . str_replace( ':', '_', $this->WCMethod->id ),
			$this->cacheContext()
		);
	}

	public function getSepKey(): string {
		return $this->getKey() . '_sep';
	}

	public function getShowKey(): string {
		return $this->getKey() . '_show';
	}

	public function getMinTotalKey(): string {
		return $this->getKey() . '_min_total';
	}

	public function getMaxTotalKey(): string {
		return $this->getKey() . '_max_total';
	}

	public function getLabel(): string {
		return self::getFromRuntimeCache(
			fn() => sprintf(
				'%s (%s)',
				$this->WCMethod->get_title(),
				$this->WCMethod->enabled === 'yes' ? 'enabled' : 'disabled'
			),
			$this->cacheContext()
		);
	}

	/**
	 * @feature payment_rules method_conditions
	 * @title[ru] Условия отображения платёжного метода
	 * @desc[ru] Метод оплаты будет доступен только если пользователь соответствует заданному типу (B2B/B2C) и другим условиям.
	 * @order 702
	 */

	public function isActive(): bool {
		return self::getFromRuntimeCache( function () {
			$currentUser = UsersController::getCurrentUser();
			$show = $this->getFieldValue( $this->getShowKey() );

			if ( $show === 'b2b' && ! $currentUser->isB2b() ) {
				return false;
			}

			if ( $show === 'b2c' && $currentUser->isB2b() ) {
				return false;
			}

			return true;
		}, $this->cacheContext( [ 'user_id' => get_current_user_id() ] ) );
	}

	/**
	 * @feature payment_rules amount_limits
	 * @title[ru] Ограничения по сумме заказа
	 * @desc[ru] Вы можете задать минимальные и максимальные суммы для каждого способа оплаты — это исключает ошибки и упрощает контроль.
	 * @order 703
	 */

	public function getMinOrderTotal(): float {
		return $this->getFieldValue( $this->getMinTotalKey() );
	}

	public function getMaxOrderTotal(): float {
		return $this->getFieldValue( $this->getMaxTotalKey() );
	}

	public function isEmptyMaxOrderTotal(): bool {
		return $this->isEmptyField( $this->getMaxTotalKey() );
	}

	public function getFields(): array {
		return self::getFromRuntimeCache( function () {
			return [ 
				new SeparatorField( $this->getSepKey(), $this->getLabel() ),
				( new SelectField( $this->getShowKey(), "Show for users" ) )
					->setOptions( [ 
						'b2x' => 'b2x',
						'b2c' => 'b2c',
						'b2b' => 'b2b',
					] )
					->setWidth( 34 ),
				( new NonNegativeFloatField( $this->getMinTotalKey(), 'Min Order Total' ) )
					->setDefaultValue( false )
					->setWidth( 33 ),
				( new NonNegativeFloatField( $this->getMaxTotalKey(), 'Max Order Total' ) )
					->setDefaultValue( false )
					->setWidth( 33 ),
			];
		}, $this->cacheContext() );
	}


	public static function getFieldsDefinition(): array {
		return self::getFromRuntimeCache( function () {
			$fields = [];
			foreach ( PaymentController::getPaymentMethods() as $method ) {
				$fields = array_merge( $fields, $method->getFields() );
			}
			return $fields;
		} );
	}


	public static function getFieldsDefinition2(): array {
		return self::getFromRuntimeCache( function () {
			$boxes = [];
			foreach ( PaymentController::getPaymentMethods() as $method ) {
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
