<?php

namespace JustB2b\Fields;

defined( 'ABSPATH' ) || exit;

class ImageField extends AbstractField {
	protected string $type = 'media_gallery';
	protected array $meidaTypes = [];

	public function __construct( string $key, string $label ) {
		parent::__construct( $key, $label );
		$this->setMediaTypes( [ 'image' ] );
		$this->defaultValue = '';
	}

	public function toACF($index = 0): array {
		$field = parent::toACF($index);
		$field['type'] = 'image';
		$field['return_format'] = 'id';
		$field['preview_size'] = 'thumbnail';
		$field['library'] = 'all';
		$field['default_value'] = null;
		return $field;
	}


	public function setMediaTypes( array $meidaTypes ): static {
		$this->meidaTypes = $meidaTypes;
		return $this;
	}
}
