<?php

namespace JustB2b\Controllers\Id;

use JustB2b\Traits\SingletonTrait;

defined( 'ABSPATH' ) || exit;

abstract class AbstractCustomPostController extends AbstractPostController {
	use SingletonTrait;

	protected function __construct() {
		parent::__construct();
		add_action( 'init', [ $this, 'registerPostType' ] );
	}

	public function registerPostType() {
		$postType = static::getPrefixedKey();
		$args = $this->getPostTypeArgs();
		register_post_type( $postType, $args );
	}

	/**
	 * Override this in subclasses to customize post type registration.
	 */
	protected function getPostTypeArgs(): array {
		$singleName = static::getSingleName();
		$pluralName = static::getPluralName();

		return [ 
			'label' => $singleName,
			'public' => false,
			'show_ui' => true,
			'show_in_menu' => false,
			'supports' => [ 'title' ],
			'labels' => [ 
					'name' => sprintf( __( '%s', 'justb2b' ), $pluralName ),
					'singular_name' => sprintf( __( '%s', 'justb2b' ), $singleName ),
					'add_new' => sprintf( __( 'Add New %s', 'justb2b' ), $singleName ),
					'add_new_item' => sprintf( __( 'Add New %s', 'justb2b' ), $singleName ),
					'edit_item' => sprintf( __( 'Edit %s', 'justb2b' ), $singleName ),
					'new_item' => sprintf( __( 'New %s', 'justb2b' ), $singleName ),
					'view_item' => sprintf( __( 'View %s', 'justb2b' ), $singleName ),
					'search_items' => sprintf( __( 'Search %s', 'justb2b' ), $pluralName ),
					'not_found' => sprintf( __( 'No %s found', 'justb2b' ), strtolower( $pluralName ) ),
					'not_found_in_trash' => sprintf( __( 'No %s found in Trash', 'justb2b' ), strtolower( $pluralName ) ),
				],
		];
	}
}
