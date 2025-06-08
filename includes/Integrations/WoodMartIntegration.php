<?php

namespace JustB2b\Integrations;

use JustB2b\Traits\SingletonTrait;
use JustB2b\Controllers\Key\ShippingController;

defined('ABSPATH') || exit;

/**
 * @feature-section theme_integration
 * @title[ru] Интеграция с темами и шаблонами
 * @desc[ru] JustB2B автоматически адаптируется к WoodMart.
 * @order 900
 */

/**
 * @feature theme_integration woodmart_shipping_bar
 * @title[ru] Поддержка индикатора доставки в теме WoodMart
 * @desc[ru] JustB2B подставляет правильную минимальную сумму для бесплатной доставки в прогресс-бар темы WoodMart, с учётом условий и логики B2B.
 * @order 901
 */


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
        return $method ? $method->getFreeFrom() : $value;
    }
}
