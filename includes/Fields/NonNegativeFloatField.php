<?php

namespace JustB2b\Fields;

defined( 'ABSPATH' ) || exit;

class NonNegativeFloatField extends NonNegativeNumberField {

	public function toACF(): array {
		$field = parent::toACF();
		$field['step'] = 0.01;
		return $field;
	}
}

