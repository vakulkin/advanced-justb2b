<?php

namespace JustB2b\Fields;

defined( 'ABSPATH' ) || exit;

class SeparatorField extends AbstractField {
	protected string $type = 'separator';


	public function getPostFieldOriginValue( int $postId ): mixed {
		return '';
	}
	public function getUserFieldOriginValue( int $userId ): mixed {
		return '';
	}
	public function getOptionOriginValue(): mixed {
		return '';
	}
}
