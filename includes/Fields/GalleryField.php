<?php

namespace JustB2b\Fields;

defined( 'ABSPATH' ) || exit;

class GalleryField extends AbstractField {
	protected string $type = 'media_gallery';
	protected array $meidaTypes = [];

	public function __construct( string $key, string $label ) {
		parent::__construct( $key, $label );
		$this->setMediaTypes( [ 'image' ] );
		$this->defaultValue = [];
	}

	public function setMediaTypes( array $meidaTypes ): static {
		$this->meidaTypes = $meidaTypes;
		return $this;
	}

	public function renderValue( int $parentId ): string {
		$values = $this->getPostFieldValue( $parentId );
		return implode( ',', $values );
	}

	public function getPostFieldOriginValue( int $postId ): array {
		global $wpdb;
		return self::getFromRuntimeCache(
			fn() => $this->getOriginValuesFromMetaTable( $postId, $wpdb->postmeta, 'post_id', '|value' ),
			[ 'post_id' => $postId, 'key' => $this->prefixedKey ]
		);
	}

	public function getUserFieldOriginValue( int $userId ): array {
		global $wpdb;
		return self::getFromRuntimeCache(
			fn() => $this->getOriginValuesFromMetaTable( $userId, $wpdb->usermeta, 'user_id', '|value' ),
			[ 'user_id' => $userId, 'key' => $this->prefixedKey ]
		);
	}

	public function getOptionOriginValue(): array {
		return self::getFromRuntimeCache(
			fn() => $this->getOriginValuesFromOptionsTable( '|value' ),
			[ 'key' => $this->prefixedKey ]
		);
	}


}
