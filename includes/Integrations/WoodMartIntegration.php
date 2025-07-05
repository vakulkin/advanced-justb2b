<?php

namespace JustB2b\Integrations;

use JustB2b\Traits\SingletonTrait;
use JustB2b\Models\Key\ShippingModel;

defined('ABSPATH') || exit;

/**
 * @feature-section theme_integration
 * @title[ru] Интеграция с темой WoodMart
 * @desc[ru] JustB2B автоматически подставляет минимальную сумму для бесплатной доставки в прогресс-бар WoodMart, с учётом B2B-логики.
 * @order 900
 */

/**
 * @feature theme_integration shipping_progress_bar
 * @title[ru] Прогресс-бар доставки WoodMart
 * @desc[ru] Значение для бесплатной доставки подставляется динамически — на основе условий JustB2B.
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

        $method = ShippingModel::getMethodWithMinimalFreeFrom();
        return $method ? $method->getFreeFrom() : $value;
    }
}
