<?php

namespace JustB2b\Fields;

defined( 'ABSPATH' ) || exit;

class RichTextField extends TextField {
	
	public function toACF(): array {
		$field = parent::toACF();
		$field['type'] = 'textarea';
		return $field;
	}

}
