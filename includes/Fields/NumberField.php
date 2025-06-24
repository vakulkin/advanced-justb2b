<?php

namespace JustB2b\Fields;

defined( 'ABSPATH' ) || exit;

class NumberField extends TextField {

	public function __construct( string $key, string $label ) {
		parent::__construct( $key, $label );
		$this->defaultValue = 0;
	}

	public function isEmptyValue( $id ): bool {
		return parent::isEmptyValue( $id )
			&& $this->getOriginValue( $id ) !== 0
			&& $this->getOriginValue( $id ) !== '0';
	}

	public function toACF($index = 0): array {
		$field = parent::toACF($index);
		$field['type'] = 'number';
		return $field;
	}

	public function getValue( int $postId ): float {
		return (float) parent::getValue( $postId );
	}

}
