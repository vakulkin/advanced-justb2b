<?php

namespace JustB2b\Fields;

defined( 'ABSPATH' ) || exit;

class FieldBuilder {
	public static function buildACF( array $definitions ): array {
		$fields = [];


		$index = 1;
		foreach ( $definitions as $definition ) {
			/** @var AbstractField $definition */
			$fields[] = $definition->toACF();
			$index++;
		}

		return $fields;
	}
}