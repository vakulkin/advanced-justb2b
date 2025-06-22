<?php

namespace JustB2b\Fields;

defined( 'ABSPATH' ) || exit;

abstract class AbstractAssociationField extends AbstractField {


	public function __construct( string $key, string $label ) {
		parent::__construct( $key, $label );
		$this->defaultValue = [];
	}

	public function toACF(): array {
		$field = parent::toACF();
		$field['return_format'] = 'id';
		return $field;
	}
}
