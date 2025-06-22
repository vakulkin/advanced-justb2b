<?php

namespace JustB2b\Models\Key;

use JustB2b\Controllers\Key\CheckoutController;

defined( 'ABSPATH' ) || exit;


class CheckoutModel extends AbstractKeyModel {

	protected function getSettingsId(): int {
		return CheckoutController::getSettingsId();
	}

	public static function getFieldsDefinition(): array {
		return [];
	}
}
