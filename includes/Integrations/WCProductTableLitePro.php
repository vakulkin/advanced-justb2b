<?php

namespace JustB2b\Integrations;



defined('ABSPATH') || exit;

use JustB2b\Traits\SingletonTrait;
use JustB2b\Controllers\UsersController;

class WCProductTableLitePro
{
    use SingletonTrait;

    protected function __construct()
    {
        add_filter('wcpt_cart_total_price', [$this, 'overrideCartTotalPrice'], 20);
    }

    public function overrideCartTotalPrice()
    {
        $cart = WC()->cart;
        if ($cart && !$cart->is_empty()) {
            $userController = UsersController::getInstance();
            $currentUser = $userController->getCurrentUser();
            $total = 0;
            if ($currentUser->isB2b()) {
                foreach ($cart->get_cart() as $cart_item) {
                    $total += $cart_item['line_subtotal'];
                }
                return wc_price($total) . ' netto';
            }
            foreach ($cart->get_cart() as $cart_item) {
                $total += $cart_item['line_subtotal'] + $cart_item['line_subtotal_tax'];
            }
        }
        return wc_price($total);
    }

}

