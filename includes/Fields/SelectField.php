<?php

namespace JustB2b\Fields;

defined( 'ABSPATH' ) || exit;

use Carbon_Fields\Field\Field;

class SelectField extends TextField {
	protected string $type = 'select';
	protected array $options = [];

	public function getOptions(): array {
		return $this->options;
	}

	public function setOptions( array $options ): static {
		$this->options = $options;
		if ( ! empty( $options ) ) {
			$this->setDefaultValue( array_key_first( $options ) );
		}
		return $this;
	}

	public function toCarbonField(): Field {
		/** @var Field $field */
		$field = parent::toCarbonField();
		if ( ! empty( $this->options ) ) {
			$field->add_options( $this->options );
		}

		return $field;
	}
}
