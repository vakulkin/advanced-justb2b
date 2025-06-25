<?php

namespace JustB2b\Integrations;

use JustB2b\Traits\SingletonTrait;
use JustB2b\Controllers\Id\UsersController;

defined( 'ABSPATH' ) || exit;

/**
 * @feature-section wpbakery_integration
 * @title[ru] Интеграция с WPBakery
 * @desc[ru] JustB2B добавляет шорткод для вывода баннеров пользователя в формате WPBakery-карусели.
 * @order 910
 */

/**
 * @feature wpbakery_integration user_banners_shortcode
 * @title[ru] Карусель баннеров WPBakery
 * @desc[ru] Шорткод [justb2b_wpbakery_user_banners] автоматически подставляет баннеры текущего пользователя в формате vc_images_carousel.
 * @order 911
 */


class WPBakeryIntegration {
	use SingletonTrait;

	protected function __construct() {
		add_shortcode( 'justb2b_wpbakery_user_banners', [ $this, 'getWPBakeryBanners' ] );
	}

	public function getWPBakeryBanners(): string {
		$currentUser = UsersController::getCurrentUser();
		$banners = $currentUser->getUserBanners();
		if ( count( $banners ) ) {
			$images = implode( ',', $banners );
			return do_shortcode( "[vc_images_carousel images=\"{$images}\" img_size=\"full\" autoplay=\"yes\"]" );
		}
		return '';
	}
}
