<?php

namespace JustB2b\Fields;

defined( 'ABSPATH' ) || exit;

class FieldBuilder {
	public static function buildACF( array $definitions ): array {
		$fields = [];

		/** @var AbstractField $fiedefinitionld */
		foreach ( $definitions as $index => $definition ) {
			$fields[] = $definition->toACF( $index + 1 );
		}

		return $fields;
	}
}