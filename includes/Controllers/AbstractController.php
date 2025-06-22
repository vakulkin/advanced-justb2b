<?php

namespace JustB2b\Controllers;

use JustB2b\Traits\SingletonTrait;
use JustB2b\Utils\Prefixer;

defined( 'ABSPATH' ) || exit;

abstract class AbstractController {
	use SingletonTrait;

	protected function __construct() {
		$this->registerACF();
	}

	abstract public function registerACF(): void;
	abstract public static function getKey();
	public static function getPrefixedKey(): string {
		return Prefixer::getPrefixed( static::getKey() );
	}

	abstract public static function getSingleName(): string;
	abstract public static function getPluralName(): string;


}
