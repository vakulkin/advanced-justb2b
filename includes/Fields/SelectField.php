<?php

namespace JustB2b\Fields;

defined( 'ABSPATH' ) || exit;

class SelectField extends AbstractOptionsField {

	public function setOptions( array $options ): static {
		parent::setOptions($options);
		if ( ! empty( $options ) ) {
			$this->setDefaultValue( array_key_first( $options ) );
		}
		return $this;
	}

	public function toACF(): array {
		$field = parent::toACF();
		$field['type'] = 'select';
		$field['default_value'] = $this->defaultValue ?? array_key_first( $this->options );
		return $field;
	}

}
