<?php

namespace JustB2b\Integrations;

defined('ABSPATH') || exit;

use JustB2b\Traits\SingletonTrait;
use JustB2b\Controllers\Id\UsersController;

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
