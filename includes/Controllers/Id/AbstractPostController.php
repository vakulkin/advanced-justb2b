<?php

namespace JustB2b\Controllers\Id;

use JustB2b\Fields\FieldBuilder;
use JustB2b\Traits\SingletonTrait;

defined( 'ABSPATH' ) || exit;


abstract class AbstractPostController extends AbstractIdController {
	use SingletonTrait;

	protected function __construct() {
		parent::__construct();
	}

	public function registerACF(): void {
		$default_lang = apply_filters( 'wpml_default_language', null );
		$current_lang = apply_filters( 'wpml_current_language', null );

		if ( $default_lang !== $current_lang ) {
			return;
		}

		if ( ! function_exists( 'acf_add_local_field_group' ) ) {
			return;
		}
		$fields = FieldBuilder::buildACF( $this->getDefinitions() );

		if ( ! empty( $fields ) ) {
			$params = [ 
				'key' => static::getKey(),
				'title' => static::getKey(),
				'fields' => $fields,
				'location' => [ 
					[ 
						[ 
							'param' => 'post_type',
							'operator' => '==',
							'value' => static::getPrefixedKey(),
						],
					],
				],
			];

			acf_add_local_field_group( $params );
		}
	}
}
