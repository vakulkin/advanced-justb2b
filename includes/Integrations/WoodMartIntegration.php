<?php

namespace JustB2b\Integrations;

defined('ABSPATH') || exit;

use JustB2b\Traits\SingletonTrait;

class WoodMartIntegration
{
    use SingletonTrait;

    protected function __construct()
    {
        add_filter('woodmart_shipping_progress_bar_amount', [$this, 'customShippingProgressBarLimit']);
    }

    public function customShippingProgressBarLimit($limit)
    {
        $shipping_methods = WC()->shipping->get_shipping_methods();
        // error_log(print_r($shipping_methods, true));
        // error_log('-------------');

        // $settings = SettingsModel::get_instance();
        // if ($settings->getOptionByKey('b2b__shipping') === 'yes') {
        //     $user_id = get_current_user_id();
        //     if ($user_id && RolesModel::isUserB2b($user_id)) {
        //         $free_from = $settings->getOptionByKey('b2b__shipping_free_from');
        //         $free_from = Sanitizer::getFloat($free_from);
        //         if ($free_from > 0) {
        //             return $free_from;
        //         }
        //     }
        // }
        return $limit;
    }

}