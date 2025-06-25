<?php

namespace JustB2b\Models\Id;

use JustB2b\Models\AbstractModel;
use JustB2b\Fields\AbstractField;
use JustB2b\Utils\Prefixer;

defined( 'ABSPATH' ) || exit;

abstract class AbstractIdModel extends AbstractModel {
	protected int $id;

	public function __construct( int $id ) {
		$this->initId( $id );
	}

	public function getId(): int {
		return $this->id;
	}

	protected function initId( int $id ): void {
		$this->id = $id;
	}

	// protected function getUserTypeClause( bool $isB2b ): array {
	// 	$clauses = [ 
	// 		'relation' => 'OR',
	// 		[ 
	// 			'key' => Prefixer::getPrefixed( 'customer_type' ),
	// 			'value' => $isB2b ? [ 'b2b', 'b2x' ] : [ 'b2c', 'b2x' ],
	// 			'compare' => 'IN',
	// 		],
	// 	];
	// 	// todo: add empty logic

	// 	return $clauses;
	// }

	public static function getPriorityClause(): array {
		return [ 
			'priority_clause' => [ 
				'key' => Prefixer::getPrefixed( 'rule_priority' ),
				'type' => 'NUMERIC',
			],
		];
	}

	public static function getBaseMetaQuery( bool $isB2b ): array {
		return array_merge(
			self::getPriorityClause()
			// , self::getUserTypeClause($isB2b) // TODO: uncomment and fix
		);
	}

	public function isEmptyField( string $key ): bool {
		/** @var AbstractField $field */
		$field = static::getField( $key );
		return $field ? $field->isEmptyValue( $this->id ) : true;
	}

	public function getFieldValue( string $key ): mixed {
		/** @var AbstractField $field */
		$field = static::getField( $key );
		// error_log(print_r($field, true));
		return $field ? $field->getValue( $this->id ) : null;
	}
}
