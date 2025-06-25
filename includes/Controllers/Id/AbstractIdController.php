<?php

namespace JustB2b\Controllers\Id;

defined( 'ABSPATH' ) || exit;

use JustB2b\Traits\SingletonTrait;
use JustB2b\Controllers\AbstractController;

abstract class AbstractIdController extends AbstractController {
	use SingletonTrait;

	protected function __construct() {
		parent::__construct();
	}
}
