<?php

namespace JustB2b\Controllers\Id;

use JustB2b\Models\Id\SettingModel;

defined( 'ABSPATH' ) || exit;

class SettingsController extends AbstractCustomPostController {
	protected function __construct() {
		parent::__construct();
		add_filter( 'post_row_actions', [ $this, 'disable_quick_edit' ], 10, 2 );
		add_action( 'add_meta_boxes', [ $this, 'replace_submitdiv_metabox' ] );

		remove_post_type_support( 'justb2b_setting', 'title' );
	}

	public static function getKey() {
		return 'setting';
	}

	public static function getSingleName(): string {
		return 'Setting';
	}

	public static function getPluralName(): string {
		return 'Settings';
	}

	public function getDefinitions(): array {
		return SettingModel::getFieldsDefinition();
	}

	public function disable_quick_edit( $actions, $post ) {
		if ( $post->post_type === 'justb2b_setting' ) {
			unset( $actions['inline hide-if-no-js'] );
		}
		return $actions;
	}

	public function replace_submitdiv_metabox() {
		remove_meta_box( 'submitdiv', 'justb2b_setting', 'side' );

		add_meta_box(
			'justb2b-submitdiv',
			__( 'Update', 'justb2b' ),
			[ $this, 'render_submitdiv' ],
			'justb2b_setting',
			'side',
			'high'
		);
	}

	public function render_submitdiv( $post ) {
		submit_button( __( 'Publish', 'justb2b' ), 'primary', 'publish', false );
	}

}
