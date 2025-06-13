<?php

namespace JustB2b\Integrations;

use JustB2b\Traits\SingletonTrait;

defined('ABSPATH') || exit;

class WCMLIntegration
{
    use SingletonTrait;

    protected function __construct()
    {
        add_filter('wcml_multi_currency_ajax_actions', function ($actions) {
            $actions[] = 'justb2b_calculate_price';
            return $actions;
        });

        add_action('add_meta_boxes', function () {
            $default_lang = apply_filters('wpml_default_language', null);
            $current_lang = apply_filters('wpml_current_language', null);

            if ($default_lang === $current_lang) {
                return;
            }

            remove_meta_box( 'carbon_fields_container_products', 'product', 'side' );
        }, 100);
    }
}
