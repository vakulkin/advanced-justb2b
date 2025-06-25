<?php

namespace JustB2b\Models\Key;

use JustB2b\Controllers\Key\CartController;
use JustB2b\Fields\SelectField;

defined( 'ABSPATH' ) || exit;

/**
 * @feature-section cart_visibility
 * @title[ru] Настройки корзины для B2B и B2C
 * @desc[ru] Управление отображением нетто и брутто цен в мини-корзине по типу клиента.
 * @order 600
 */

/**
 * @feature cart_visibility cart_model
 * @title[ru] Отображение цен в мини-корзине
 * @desc[ru] Настройка, кто видит нетто и брутто: B2B, B2C или все.
 * @order 601
 */

class CartModel extends AbstractKeyModel {


	protected function getSettingsId(): int {
		return CartController::getSettingsId();
	}

	public static function getFieldsDefinition(): array {
		return [ 
			( new SelectField( 'cart_mini_net_price', 'Mini cart net price visibility' ) )
				->setOptions( [ 
					'b2x' => 'b2x',
					'b2b' => 'b2b',
					'b2c' => 'b2c',
				] )
				->setHelpText( 'Choose who should see the net price in the mini cart.' )
				->setWidth( 50 ),

			( new SelectField( 'cart_mini_gross_price', 'Mini cart gross price visibility' ) )
				->setOptions( [ 
					'b2x' => 'b2x',
					'b2b' => 'b2b',
					'b2c' => 'b2c',
				] )
				->setHelpText( 'Choose who should see the gross price in the mini cart.', )
				->setWidth( 50 ),
		];
	}
}
