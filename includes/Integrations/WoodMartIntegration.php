<?php

namespace JustB2b\Integrations;

use JustB2b\Models\ShippingMethodModel;

defined('ABSPATH') || exit;

use JustB2b\Traits\SingletonTrait;
use JustB2b\Controllers\ShippingController;

class WoodMartIntegration
{
    use SingletonTrait;

    protected function __construct()
    {
        add_filter('woodmart_option', [$this, 'overrideWoodmartOption'], 10, 2);
    }

    public function overrideWoodmartOption($value, $slug)
    {
        if ($slug !== 'shipping_progress_bar_amount') {
            return $value;
        }

        $method = ShippingMethodModel::getMethodWithMinimalFreeFrom();

        return $method !== false ? $method->getFreeFrom() : $value;
    }
}
