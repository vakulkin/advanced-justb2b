<?php

namespace JustB2b\Fields;

defined( 'ABSPATH' ) || exit;

class NonNegativeFloatField extends NonNegativeNumberField {

	public function toACF($index = 0): array {
		$field = parent::toACF($index);
		$field['step'] = 0.01;
		return $field;
	}
}

