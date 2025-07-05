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

	public static function getKeyFieldsDefinition(): array {
		$keyFields = [];
		foreach ( static::getFieldsDefinition() as $index => $field ) {
			$field->setIndex( $index + 1 ); // ACF expects 1-based index
			$keyFields[ $field->getKey()] = $field;
		}
		return $keyFields;
	}

	public static function getField( string $key ): ?object {
		/** @var AbstractField|null $field */
		return static::getKeyFieldsDefinition()[ $key ] ?? null;
	}
}
