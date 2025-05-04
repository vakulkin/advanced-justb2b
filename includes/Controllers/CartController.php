<?php

namespace JustB2b\Controllers;

use WC_Cart;
use JustB2b\Traits\SingletonTrait;
use JustB2b\Models\ProductModel;

defined('ABSPATH') || exit;

class CartController
{
    use SingletonTrait;

    public function __construct()
    {
        add_action('woocommerce_before_calculate_totals', [$this, 'handleCartTotals'], 20);
    }

    public function handleCartTotals(WC_Cart $cart): void
    {
        if (is_admin() && !defined('DOING_AJAX')) {
            return;
        }

        foreach ($cart->get_cart() as $cart_item_key => $cart_item) {
            $product = $cart_item['data'];
            $product_id = $product->get_id();
            $qty = $cart_item['quantity'];

            // Load ProductModel with quantity
            $productModel = new ProductModel($product_id, $qty);
            if (!$productModel->isSimpleProduct()) {
                continue;
            }

            $rule = $productModel->getFirstFullFitRule();

            // If no rule or the product isn't purchasable, skip it
            if (!$rule) {
                continue;
            }

            $priceCalculator = $productModel->getPriceCalculator();
            $baseGrossPrice = $priceCalculator->getBaseGrossPrice();
            $finalPrice = $priceCalculator->getFinalGrossPrice();

            // Apply the price
            if ($baseGrossPrice > $finalPrice) {
                $product->set_regular_price($baseGrossPrice);
            }
            
            $product->set_price($finalPrice);
            $product->set_sale_price($finalPrice);
        }
    }
}
