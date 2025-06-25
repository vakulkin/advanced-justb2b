<?php

namespace JustB2b\Controllers;

use JustB2b\Traits\SingletonTrait;
use JustB2b\Utils\Prefixer;
use JustB2b\Fields\AbstractField;

defined( 'ABSPATH' ) || exit;

abstract class AbstractController {
	use SingletonTrait;

	protected function __construct() {
		add_action( 'acf/init', [ $this, 'registerACF' ], 0 );
	}

	abstract public function registerACF(): void;
	abstract public static function getKey();
	public static function getPrefixedKey(): string {
		return Prefixer::getPrefixed( static::getKey() );
	}

	abstract public static function getSingleName(): string;
	abstract public static function getPluralName(): string;
	abstract public function getDefinitions(): array;

	public function getField( string $key ): ?object {
		/** @var AbstractField|null $field */
		return $this->getDefinitions()[ $key ] ?? null;
	}
}
