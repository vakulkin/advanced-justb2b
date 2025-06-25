<?php

namespace JustB2b\Fields;

defined( 'ABSPATH' ) || exit;

class SeparatorField extends AbstractField {
	
	public function toACF(): array {
		$field = parent::toACF();
		$field['type'] = 'separator';
		return $field;
	}
}
