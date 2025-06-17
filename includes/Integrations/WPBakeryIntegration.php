<?php

namespace JustB2b\Integrations;

use JustB2b\Traits\SingletonTrait;
use JustB2b\Controllers\Id\UsersController;

defined('ABSPATH') || exit;

class WPBakeryIntegration
{
    use SingletonTrait;

    protected function __construct()
    {
        add_action('init', function (): void {
            add_shortcode('justb2b_wpbakery_user_banners', [$this, 'getWPBakeryBanners']);
        });
    }

    public function getWPBakeryBanners(): string
    {
        $userController = UsersController::getInstance();
        $currentUser = $userController->getCurrentUser();
        if (count($currentUser->getUserBanners())) {
            $images = implode(',', $currentUser->getUserBanners());
            return do_shortcode("[vc_images_carousel images=\"{$images}\" img_size=\"full\" autoplay=\"yes\"]");
        }
        return '';
    }
}
