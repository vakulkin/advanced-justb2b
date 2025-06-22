<?php

namespace JustB2b\Models\Key;

use JustB2b\Controllers\Key\CartController;
use JustB2b\Fields\SelectField;

defined( 'ABSPATH' ) || exit;


/**
 * @feature-section cart_visibility
 * @title[ru] Настройки корзины для B2B и B2C
 * @desc[ru] JustB2B позволяет настроить, какие цены (нетто или брутто) должны отображаться в мини-корзине — в зависимости от типа клиента.
 * @order 600
 */

/**
 * @feature cart_visibility cart_model
 * @title[ru] Гибкое отображение цен в мини-корзине
 * @desc[ru] Вы можете задать, кто видит нетто- и брутто-цены в мини-корзине: B2B, B2C или все. Это помогает соблюдать юридические и UX-требования.
 * @order 601
 */

class CartModel extends AbstractKeyModel {


	protected function getSettingsId(): int {
		return CartController::getSettingsId();
	}

	public static function getFieldsDefinition(): array {
		return [ 
			( new SelectField( 'mini_cart_net_price', 'Mini cart net price visibility' ) )
				->setOptions( [ 
					'b2x' => 'b2x',
					'b2b' => 'b2b',
					'b2c' => 'b2c',
				] )
				->setHelpText( 'Choose who should see the net price in the mini cart.' )
				->setWidth( 50 ),

			( new SelectField( 'mini_cart_gross_price', 'Mini cart gross price visibility' ) )
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
