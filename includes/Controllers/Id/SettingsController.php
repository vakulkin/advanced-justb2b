<?php

namespace JustB2b\Controllers\Id;

use JustB2b\Models\Id\SettingModel;
use WP_Admin_Bar;

defined( 'ABSPATH' ) || exit;

class SettingsController extends AbstractCustomPostController {
	protected function __construct() {
		parent::__construct();

		add_action( 'admin_menu', [ $this, 'addSettingsPage' ] );
		add_action( 'admin_bar_menu', [ $this, 'addTopBarItem' ], 100 );


		add_action( 'add_meta_boxes', [ $this, 'replace_submitdiv_metabox' ] );

		add_action( 'all_admin_notices', [ $this, 'renderButtonsOnListScreens' ] );
		add_action( 'all_admin_notices', [ $this, 'renderButtonsAboveSingleEdit' ] );





		// add_action( 'restrict_manage_posts', function () {
		// 	$screen = get_current_screen();
		// 	if ( $screen && $screen->post_type === static::getPrefixedKey() ) {
		// 		remove_all_actions( 'restrict_manage_posts' );
		// 	}
		// }, 9 ); // Run before core hooks (which run at 10)


		// add_action( 'admin_init', function () {
		// 	$prefixedKey = static::getPrefixedKey();
		// 	add_filter( "bulk_actions-edit-{$prefixedKey}", '__return_empty_array' );
		// 	add_filter( "views_edit-{$prefixedKey}", '__return_empty_array' );
		// } );



		// add_filter( 'disable_months_dropdown', function ($disable, $post_type) {
		// 	return $post_type === static::getPrefixedKey() ? true : $disable;
		// }, 10, 2 );


		add_filter( 'map_meta_cap', function ($caps, $cap, $user_id, $args) {
			$target_post_type = static::getPrefixedKey();
			$plural = "{$target_post_type}s";

			// Common caps to deny
			$deny_caps = [ 
				"create_{$plural}",
				"delete_{$target_post_type}",
				"delete_{$plural}",
				"delete_others_{$plural}",
				"delete_private_{$plural}",
				"delete_published_{$plural}",
				"publish_{$plural}",
			];

			if ( in_array( $cap, $deny_caps, true ) ) {
				return [ 'do_not_allow' ];
			}

			// Allow edit capabilities
			$edit_caps = [ 
				"edit_{$target_post_type}",
				"edit_{$plural}",
				"edit_others_{$plural}",
				"edit_post",
			];

			if ( in_array( $cap, $edit_caps, true ) ) {
				return [ 'read' ];
			}

			return $caps;
		}, 10, 4 );
	}

	public function renderButtonsOnListScreens(): void {
		$screen = get_current_screen();
		if (
			! $screen
			|| ! in_array( $screen->post_type, [ 'justb2b_role', 'justb2b_rule' ], true )
			|| $screen->base !== 'edit'
		) {
			return;
		}
		echo '<div style="margin-bottom: 16px;">' . $this->getSettingsButtonsHtml() . '</div>';
	}


	public function addSettingsPage(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		add_menu_page(
			__( 'JustB2B Settings', 'justb2b' ),
			__( 'JustB2B', 'justb2b' ),
			'manage_options',
			'justb2b-settings',
			[ $this, 'renderSettingsPage' ],
			'dashicons-admin-generic',
			60
		);
	}


	public function addTopBarItem( WP_Admin_Bar $admin_bar ): void {
		if ( ! current_user_can( 'manage_options' ) || ! is_admin_bar_showing() ) {
			return;
		}

		$admin_bar->add_node( [ 
			'id' => 'justb2b-settings',
			'title' => __( 'B2B', 'justb2b' ),
			'href' => admin_url( 'admin.php?page=justb2b-settings' ),
			'meta' => [ 
				'title' => __( 'Go to JustB2B Settings', 'justb2b' ),
			],
		] );
	}

	public function renderSettingsPage(): void {
		echo self::getSettingsPageHtml();
	}

	public function getSettingsPageHtml(): string {
		return '<div class="wrap">
		<h1 class="wp-heading-inline">' . esc_html__( 'JustB2B Settings', 'justb2b' ) . '</h1>
		' . $this->getSettingsButtonsHtml() . '
	</div>';
	}

	public function getSettingsButtonsHtml( ?int $activeId = null ): string {
		$args = [ 
			'post_type' => 'justb2b_setting',
			'post_status' => 'publish',
			'posts_per_page' => -1,
			'orderby' => 'title',
			'order' => 'ASC',
		];

		$settings = get_posts( $args );
		$buttons = [];
		$current_screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		$is_general_page = $current_screen && $current_screen->base === 'toplevel_page_justb2b-settings';

		$index = 1;

		// Info button
		$buttons[] = sprintf(
			'<a href="%s" class="justb2b-button %s" data-index="%d">%s</a>',
			esc_url( admin_url( 'admin.php?page=justb2b-settings' ) ),
			$is_general_page ? 'is-active' : '',
			$index++,
			esc_html__( 'JustB2B', 'justb2b' )
		);

		// Manage Roles
		$buttons[] = sprintf(
			'<a href="%s" class="justb2b-button" data-index="%d">%s</a>',
			esc_url( admin_url( 'edit.php?post_type=justb2b_role' ) ),
			$index++,
			esc_html__( 'roles', 'justb2b' )
		);

		// Manage Rules
		$buttons[] = sprintf(
			'<a href="%s" class="justb2b-button" data-index="%d">%s</a>',
			esc_url( admin_url( 'edit.php?post_type=justb2b_rule' ) ),
			$index++,
			esc_html__( 'rules', 'justb2b' )
		);

		// Settings posts
		foreach ( $settings as $setting ) {
			$is_active = $activeId && (int) $setting->ID === (int) $activeId;
			$url = get_edit_post_link( $setting->ID );
			$title = get_the_title( $setting );

			$buttons[] = sprintf(
				'<a href="%s" class="justb2b-button %s" data-index="%d">%s</a>',
				esc_url( $url ),
				$is_active ? 'is-active' : '',
				$index++,
				esc_html__( $title, 'justb2b' )
			);
		}



		return '<div class="justb2b-buttons" style="margin-top: 24px; display: flex; flex-wrap: wrap; gap: 12px;">'
			. implode( '', $buttons ) .
			'</div>';
	}



	public function renderButtonsAboveSingleEdit(): void {
		$screen = get_current_screen();
		if (
			! $screen
			|| $screen->base !== 'post'
			|| ! in_array( $screen->post_type, [ 'product', 'justb2b_setting', 'justb2b_role', 'justb2b_rule' ], true )
		) {
			return;
		}

		$post_id = isset( $_GET['post'] ) ? (int) $_GET['post'] : null;

		echo '<div class="wrap">
		<h2 class="screen-reader-text">JustB2B Navigation</h2>
		' . $this->getSettingsButtonsHtml( $screen->post_type === 'justb2b_setting' ? $post_id : null ) . '
	</div>';
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

	protected function getPostTypeArgs(): array {
		$args = parent::getPostTypeArgs();
		$singleKey = static::getPrefixedKey();
		$pluralKey = "{$singleKey}s";

		if ( is_admin() && isset( $_GET['post'] ) && isset( $_GET['action'] ) && 'edit' === $_GET['action'] ) {
			$post_id = (int) $_GET['post'];
			$args['labels']['edit_item'] = get_the_title( $post_id );
		}

		$args['show_in_menu'] = false;
		$args['supports'] = [ 'nothing' ];
		$args['capability_type'] = [ $singleKey, $pluralKey ];
		$args['map_meta_cap'] = true;
		$args['capabilities'] = [ 
			'edit_post' => "edit_{$singleKey}",
			'read_post' => "read_{$singleKey}",
			'delete_post' => "delete_{$singleKey}",
			'edit_posts' => "edit_{$pluralKey}",
			'edit_others_posts' => "edit_others_{$pluralKey}",
			'publish_posts' => "publish_{$pluralKey}",
			'read_private_posts' => "read_private_{$pluralKey}",
			'create_posts' => "create_{$pluralKey}",
			'delete_posts' => "delete_{$pluralKey}",
			'delete_others_posts' => "delete_others_{$pluralKey}",
			'delete_published_posts' => "delete_published_{$pluralKey}",
			'delete_private_posts' => "delete_private_{$pluralKey}",
		];
		return $args;
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
