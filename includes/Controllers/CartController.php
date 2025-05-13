<?php

namespace JustB2b\Controllers;

use WC_Cart;
use JustB2b\Models\CartModel;
use JustB2b\Utils\Prefixer;
use JustB2b\Fields\FieldBuilder;
use JustB2b\Traits\SingletonTrait;
use JustB2b\Traits\RuntimeCacheTrait;
use JustB2b\Models\ProductModel;

defined('ABSPATH') || exit;

class CartController extends AbstractKeyController
{
    use SingletonTrait;
    use RuntimeCacheTrait;

    protected string $modelClass = CartModel::class;

    protected function __construct()
    {
        parent::__construct();

        add_filter('woocommerce_widget_cart_item_quantity', [$this, 'miniCartPriceFilter'], 10, 2);
        add_action('woocommerce_before_calculate_totals', [$this, 'handleCartTotals'], 20);
    }

    public function registerCarbonFields()
    {
        $definitions = $this->modelClass::getFieldsDefinition();
        $fields = FieldBuilder::buildFields($definitions);

        $globalController = GlobalController::getInstance();
        $generalSettings = $globalController->getGlobalSettings();

        $generalSettings->add_tab('Cart', $fields);
    }

    public function getShowNetFor(): string
    {
        return $this->getFromRuntimeCache('mini_cart_net_price', function () {
            return get_option(Prefixer::getPrefixedMeta('mini_cart_net_price')) ?: 'b2x';
        });
    }

    public function getShowGrossFor(): string
    {
        return $this->getFromRuntimeCache('mini_cart_gross_price', function () {
            return get_option(Prefixer::getPrefixedMeta('mini_cart_gross_price')) ?: 'b2x';
        });
    }

    public function miniCartPriceFilter($output, $cart_item)
    {
        $net_price = $cart_item['line_subtotal'] / $cart_item['quantity'];
        $gross_price = ($cart_item['line_subtotal'] + $cart_item['line_subtotal_tax']) / $cart_item['quantity'];

        $net_price_formatted = wc_price($net_price);
        $gross_price_formatted = wc_price($gross_price);

        $userController = UsersController::getInstance();
        $currentUser = $userController->getCurrentUser();
        $userKind = $currentUser->isB2b() ? 'b2b' : 'b2c';

        $showNet = $this->getShowNetFor() === $userKind || $this->getShowNetFor() === 'b2x';
        $showGross = $this->getShowGrossFor() === $userKind || $this->getShowGrossFor() === 'b2x';

        $output = '<span class="quantity">' . esc_html($cart_item['quantity']);

        if ($showNet) {
            $output .= ' &times; ' . $net_price_formatted . ' ' . esc_html__('netto', 'justb2b');
        }

        if ($showGross) {
            $output .= $showNet ? '<br />' : ' &times; ';
            $output .= $gross_price_formatted . ' ' . esc_html__('brutto', 'justb2b');
        }

        $output .= '</span>';

        return $output;
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

            $productModel = new ProductModel($product_id, $qty);
            if (!$productModel->isSimpleProduct()) {
                continue;
            }

            $rule = $productModel->getFirstFullFitRule();
            if (!$rule) {
                continue;
            }

            $priceCalculator = $productModel->getPriceCalculator();
            $baseGrossPrice = $priceCalculator->getBaseGrossPrice();
            $finalPrice = $priceCalculator->getFinalGrossPrice();

            if ($baseGrossPrice > $finalPrice) {
                $product->set_regular_price($baseGrossPrice);
            }

            $product->set_price($finalPrice);
            $product->set_sale_price($finalPrice);
        }
    }
}
