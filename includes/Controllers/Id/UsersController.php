<?php

namespace JustB2b\Controllers\Id;

use JustB2b\Fields\FieldBuilder;

defined( 'ABSPATH' ) || exit;

use JustB2b\Models\Id\UserModel;
use JustB2b\Traits\RuntimeCacheTrait;

class UsersController extends AbstractIdController {
	use RuntimeCacheTrait;

	protected function __construct() {
		parent::__construct();
		add_shortcode( 'justb2b_user_banners', [ $this, 'getUserBannersShortcode' ] );
	}

	public static function getKey() {
		return 'user';
	}

	public static function getSingleName(): string {
		return 'User';
	}

	public static function getPluralName(): string {
		return 'Users';
	}

	public function getDefinitions(): array {
		return UserModel::getFieldsDefinition();
	}
	public function getUserBannersShortcode() {
		return self::getCurrentUser()->getUserBannersHtml();
	}

	public static function getCurrentUser(): UserModel {
		return self::getFromRuntimeCache( function () {
			return new UserModel( get_current_user_id() );
		}, [ 'user_id' => get_current_user_id() ] );
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
							'param' => 'user_form',
							'operator' => '==',
							'value' => 'all',
						],
					],
				],
			];

			acf_add_local_field_group( $params );
		}
	}
}
