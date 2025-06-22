<?php

namespace JustB2b\Models;

use JustB2b\Fields\AbstractField;
use JustB2b\Traits\RuntimeCacheTrait;

defined( 'ABSPATH' ) || exit;

abstract class AbstractModel {
	use RuntimeCacheTrait;

	abstract public static function getFieldsDefinition(): array;

	protected function cacheContext( array $extra = [] ): array {
		return $extra;
	}

	public function getField( string $key ): ?object {
		/** @var AbstractField $field */
		foreach ( static::getFieldsDefinition() as $field ) {
			if ( $key == $field->getKey() ) {
				return $field;
			}
		}
		return null;
	}
}
