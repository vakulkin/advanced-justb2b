<?php

namespace JustB2b\Controllers\Key;

use JustB2b\Controllers\Key\AbstractKeyController;
use JustB2b\Models\Key\SettingsModel;

defined( 'ABSPATH' ) || exit;

class GlobalController extends AbstractKeyController {
	private string $key = 'global';
	protected SettingsModel $settingsModelObject;

	protected function __construct() {
		parent::__construct();

		$this->settingsModelObject = new SettingsModel();

		wp_cache_add_non_persistent_groups( [ 'justb2b_plugin' ] );

		add_action( 'wp_enqueue_scripts', [ $this, 'enqueueScripts' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'adminEnqueueScripts' ] );

		add_action( 'admin_head', function () {
			echo '<style>
				.acf-field.acf-field-checkbox .acf-checkbox-list {
					display: flex;
					flex-wrap: wrap;
					gap: 7px;
				}

				.acf-field.acf-field-checkbox .acf-checkbox-list::before {
					content: none;
				}
			</style>';
		} );

		add_filter( 'acf/field_wrapper_attributes', function ($wrapper, $field) {
			if ( isset( $field['index'] ) && (int) $field['index'] > 0 ) {
				$wrapper['data-index'] = (int) $field['index'];
			}
			return $wrapper;
		}, 10, 2 );
	}

	public static function getKey() {
		return 'global';
	}

	public static function getSingleName(): string {
		return 'Global';
	}

	public static function getPluralName(): string {
		return 'Globals';
	}

	public function getDefinitions(): array {
		return SettingsModel::getKeyFieldsDefinition();
	}

	public function getSettingsModelObject() {
		return $this->settingsModelObject;
	}

	public function enqueueScripts() {
		wp_enqueue_style(
			'justb2b-frontend',
			JUSTB2B_PLUGIN_URL . 'assets/css/frontend.css',
			[],
			JUSTB2B_PLUGIN_VERSION
		);

		wp_enqueue_script(
			'justb2b-product',
			JUSTB2B_PLUGIN_URL . 'assets/js/price.js',
			[ 'jquery' ],
			JUSTB2B_PLUGIN_VERSION
		);

		wp_localize_script( 'justb2b-product', 'justb2b', [ 
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'nonce' => wp_create_nonce( 'justb2b_price_nonce' ),
		] );
	}

	public function adminEnqueueScripts() {
		wp_enqueue_style(
			'justb2b-backend',
			JUSTB2B_PLUGIN_URL . 'assets/css/backend.css',
			[],
			JUSTB2B_PLUGIN_VERSION
		);
	}
}
