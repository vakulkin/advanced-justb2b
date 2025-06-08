<?php

namespace JustB2b\Integrations;

use JustB2b\Traits\SingletonTrait;
use JustB2b\Controllers\Id\UsersController;

defined('ABSPATH') || exit;

/**
 * @feature-section wcpt_integration
 * @title[ru] Интеграция с таблицами продуктов (WCPT)
 * @desc[ru] JustB2B корректно работает с плагинами таблиц продуктов, такими как WooCommerce Product Table Lite/Pro, заменяя цену на соответствующую B2B или B2C.
 * @order 800
 */

/**
 * @feature wcpt_integration correct_cart_total
 * @title[ru] Корректный итог корзины в таблице
 * @desc[ru] В таблицах продуктов отображается итоговая сумма с учётом B2B- и B2C-логики: нетто или брутто, в зависимости от пользователя.
 * @order 801
 */

/**
 * @feature wcpt_integration user_type_based_total
 * @title[ru] Учет типа пользователя (B2B / B2C)
 * @desc[ru] Итоговая сумма рассчитывается в зависимости от роли пользователя: B2B видят нетто, B2C — брутто.
 * @order 802
 */


class WCProductTableLitePro
{
    use SingletonTrait;

    protected function __construct()
    {
        add_filter('wcpt_cart_total_price', [$this, 'overrideCartTotalPrice'], 20);
    }

    public function overrideCartTotalPrice(): string
    {
        $cart = WC()->cart;

        if (!$cart || $cart->is_empty()) {
            return wc_price(0);
        }

        $userController = UsersController::getInstance();
        $currentUser = $userController->getCurrentUser();

        $total = 0;

        foreach ($cart->get_cart() as $cart_item) {
            if ($currentUser->isB2b()) {
                $total += $cart_item['line_subtotal'];
            } else {
                $total += $cart_item['line_subtotal'] + $cart_item['line_subtotal_tax'];
            }
        }

        return wc_price($total) . ($currentUser->isB2b() ? ' netto' : '');
    }
}
