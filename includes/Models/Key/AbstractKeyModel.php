<?php

namespace JustB2b\Models\Key;

use JustB2b\Models\AbstractModel;
use JustB2b\Fields\AbstractField;

defined( 'ABSPATH' ) || exit;

abstract class AbstractKeyModel extends AbstractModel {

	abstract protected function getSettingsId(): int;

	public function isEmptyField( string $key ): bool {
		/** @var AbstractField $field */
		$field = static::getField( $key );
		return $field ? $field->isEmptyValue( $this->getSettingsId() ) : true;
	}

	public function getFieldValue( string $key ): mixed {
		/** @var AbstractField $field */
		$field = static::getField( $key );
		return $field ? $field->getValue( $this->getSettingsId() ) : null;
	}
}
