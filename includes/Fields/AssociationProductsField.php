<?php

namespace JustB2b\Fields;

defined( 'ABSPATH' ) || exit;

class AssociationProductsField extends AbstractAssociationPostsField {
	public function __construct( string $key, string $label ) {
		parent::__construct( $key, $label );
		$this->setPostTypes( [ 'product' ] );
	}
}
