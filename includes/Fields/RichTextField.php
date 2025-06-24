<?php

namespace JustB2b\Fields;

defined( 'ABSPATH' ) || exit;

class RichTextField extends TextField {
	
	public function toACF($index = 0): array {
		$field = parent::toACF($index);
		$field['type'] = 'textarea';
		return $field;
	}

}
