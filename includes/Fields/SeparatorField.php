<?php

namespace JustB2b\Fields;

defined( 'ABSPATH' ) || exit;

class SeparatorField extends AbstractField {
	
	public function toACF($index = 0): array {
		$field = parent::toACF($index);
		$field['type'] = 'separator';
		return $field;
	}
}
