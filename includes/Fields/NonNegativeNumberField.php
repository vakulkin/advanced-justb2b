<?php

namespace JustB2b\Fields;

defined( 'ABSPATH' ) || exit;

class NonNegativeNumberField extends NumberField {

	public function toACF(): array {
		$field = parent::toACF();
		$field['min'] = 0;
		return $field;
	}

	public function getValue( int $postId ): float {
		return abs( parent::getValue( $postId ) );
	}
}
