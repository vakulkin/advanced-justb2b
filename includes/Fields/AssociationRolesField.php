<?php

namespace JustB2b\Fields;

use JustB2b\Utils\Prefixer;

defined( 'ABSPATH' ) || exit;

class AssociationRolesField extends AbstractAssociationPostsField {
	public function __construct( string $key, string $label ) {
		parent::__construct( $key, $label );

		$this->setPostTypes( [ Prefixer::getPrefixed( 'role' ) ] );
	}
}
