<?php

namespace JustB2b\Models\Key\Method;

use WC_Payment_Gateway;
use JustB2b\Models\Key\PaymentModel;
use JustB2b\Controllers\Key\PaymentController;
use JustB2b\Models\Key\AbstractKeyModel;
use JustB2b\Controllers\Id\UsersController;
use JustB2b\Fields\SelectField;
use JustB2b\Fields\SeparatorField;
use JustB2b\Fields\NonNegativeFloatField;
use JustB2b\Traits\RuntimeCacheTrait;

defined( 'ABSPATH' ) || exit;

/**
 * @feature-section payment_rules
 * @title[ru] Настройка платёжных методов
 * @title[pl] Konfiguracja metod płatności
 * @desc[ru] Управление доступностью оплаты по типу клиента и сумме заказа.
 * @desc[pl] Zarządzanie dostępnością metod płatności w zależności od typu klienta i wartości zamówienia.
 * @order 700
 */

/**
 * @feature payment_rules method_visibility
 * @title[ru] Отображение методов оплаты по клиенту
 * @title[pl] Widoczność metod płatności w zależności od klienta
 * @desc[ru] Делайте способы оплаты доступными для B2B или B2C в зависимости от условий.
 * @desc[pl] Udostępniaj metody płatności klientom B2B lub B2C w zależności od warunków.
 * @order 701
 */

/**
 * @feature payment_rules amount_conditions
 * @title[ru] Доступность методов оплаты по сумме заказа
 * @title[pl] Dostępność metod płatności w zależności od wartości zamówienia
 * @desc[ru] Ограничивайте доступ к способам оплаты по минимальной и максимальной сумме.
 * @desc[pl] Ograniczaj dostępność metod płatności według minimalnej i maksymalnej wartości zamówienia.
 * @order 702
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
		// todo: fix repeating definition
		return PaymentModel::getFieldsDefinition();
	}

}
