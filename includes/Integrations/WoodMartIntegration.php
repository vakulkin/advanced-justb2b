<?php

namespace JustB2b\Integrations;

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

        $shippingController = ShippingController::getInstance();
        $method = $shippingController->getMethodWithMinimalFreeFrom();

        return $method !== false ? $method->getFreeFrom() : $value;
    }
}
