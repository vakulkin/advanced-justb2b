<?php

namespace JustB2b\Fields;

defined( 'ABSPATH' ) || exit;

class TextField extends AbstractField {
	protected string $type = 'text';

	protected function isEmpty( $value ): bool {
		return parent::isEmpty( $value ) || $value === '';
	}

	public function getPostFieldOriginValue( int $postId ): mixed {
		return self::getFromRuntimeCache(
			fn() => get_post_meta( $postId, $this->prefixedMetaKey, true ),
			[ 'post_id' => $postId, 'key' => $this->prefixedKey ]
		);
	}

	public function getUserFieldOriginValue( int $userId ): mixed {
		return self::getFromRuntimeCache(
			fn() => get_user_meta( $userId, $this->prefixedMetaKey, true ),
			[ 'user_id' => $userId, 'key' => $this->prefixedKey ]
		);

	}

	public function getOptionOriginValue(): mixed {
		return self::getFromRuntimeCache(
			fn() => get_option( $this->prefixedMetaKey ),
			[ 'key' => $this->prefixedKey ]
		);
	}
}
