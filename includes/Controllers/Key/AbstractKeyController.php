<?php

namespace JustB2b\Controllers\Key;

use JustB2b\Controllers\AbstractController;
use JustB2b\Fields\FieldBuilder;

defined( 'ABSPATH' ) || exit;

abstract class AbstractKeyController extends AbstractController {

	protected function __construct() {
		parent::__construct();
		register_activation_hook( JUSTB2B_PLUGIN_FILE, [ $this, 'create_setting_post' ] );
		do_action( 'wpml_register_post_type_translation', static::getPrefixedKey(), [ 'translate' => false ] );

	}

	public function create_setting_post() {
		$post_type = 'justb2b_setting';

		$existing = get_posts( [ 
			'name' => static::getKey(),
			'post_type' => $post_type,
			'post_status' => 'any',
			'numberposts' => 1,
			'fields' => 'ids',
			'suppress_filters' => false,
			'lang' => '',
		] );

		if ( empty( $existing ) ) {
			wp_insert_post( [ 
				'post_title' => static::getKey(),
				'post_name' => static::getKey(),
				'post_type' => $post_type,
				'post_status' => 'publish',
			] );
		}
	}

	public static function getSettingsId(): int {
		return self::getPostIdBySlug( static::getKey(), 'justb2b_setting' );
	}

	public static function getPostIdBySlug( string $slug, string $post_type ): ?int {
		$post = get_page_by_path( $slug, OBJECT, $post_type );
		return $post ? (int) $post->ID : null;
	}

	public function registerACF(): void {

		if ( function_exists( 'acf_add_local_field_group' ) ) {
			$fields = FieldBuilder::buildACF( $this->getDefinitions() );
			$params = [ 
				'key' => static::getKey(),
				'title' => static::getKey(),
				'fields' => $fields,
				'location' => [ 
					[ 
						[ 
							'param' => 'post',
							'operator' => '==',
							'value' => self::getSettingsId(),
						],
					],
				],
			];

			acf_add_local_field_group( $params );
		}
	}

	abstract public function getDefinitions(): array;
}
