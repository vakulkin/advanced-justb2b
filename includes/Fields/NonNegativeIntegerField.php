<?php

namespace JustB2b\Fields;

defined( 'ABSPATH' ) || exit;

class NonNegativeIntegerField extends NonNegativeNumberField {

	public function toACF($index = 0): array {
		$field = parent::toACF($index);
		$field['step'] = 1;
		return $field;
	}

	public function getValue( int $postId ): float {
		return (int) parent::getValue( $postId );
	}
}
