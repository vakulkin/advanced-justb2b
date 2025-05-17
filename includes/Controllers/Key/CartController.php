<?php

namespace JustB2b\Controllers\Key;

use WC_Cart;
use JustB2b\Controllers\Key\AbstractKeyController;
use JustB2b\Controllers\Id\UsersController;
use JustB2b\Models\Id\ProductModel;
use JustB2b\Models\Key\CartModel;
use JustB2b\Fields\SelectField;
use JustB2b\Fields\FieldBuilder;
use JustB2b\Traits\SingletonTrait;
use JustB2b\Traits\RuntimeCacheTrait;

defined('ABSPATH') || exit;

class CartController extends AbstractKeyController
{
    use SingletonTrait;
    use RuntimeCacheTrait;
    protected CartModel $cartModelObject;
    protected function __construct()
    {
        parent::__construct();

        $this->cartModelObject = new CartModel();

        add_filter('woocommerce_widget_cart_item_quantity', [$this, 'miniCartPriceFilter'], 10, 2);
        add_action('woocommerce_before_calculate_totals', [$this, 'handleCartTotals'], 20);
    }

    public function getModelObject()
    {
        return $this->cartModelObject;
    }

    public function registerCarbonFields(): void
    {
        $fields = FieldBuilder::buildFields(
            $this->getFieldsDefinition()
        );

        $globalController = GlobalController::getInstance();
        $generalSettings = $globalController->getGlobalSettings();

        $generalSettings->add_tab('Cart', $fields);
    }

    public function getShowNetFor(): string
    {

        return $this->cartModelObject->getFieldValue('mini_cart_net_price');
    }

    public function getShowGrossFor(): string
    {
        return $this->cartModelObject->getFieldValue('mini_cart_gross_price');
    }

    public function miniCartPriceFilter($output, $cart_item): string
    {
        $quantity = $cart_item['quantity'];
        $netPrice = wc_price($cart_item['line_subtotal'] / $quantity);
        $grossPrice = wc_price(($cart_item['line_subtotal'] + $cart_item['line_subtotal_tax']) / $quantity);

        $userKind = UsersController::getInstance()->getCurrentUser()->isB2b() ? 'b2b' : 'b2c';

        $showNet = in_array($this->getShowNetFor(), [$userKind, 'b2x'], true);
        $showGross = in_array($this->getShowGrossFor(), [$userKind, 'b2x'], true);

        $output = '<span class="quantity">' . esc_html($quantity);

        if ($showNet) {
            $output .= ' &times; ' . $netPrice . ' ' . esc_html__('netto', 'justb2b');
        }

        if ($showGross) {
            $output .= $showNet ? '<br />' : ' &times; ';
            $output .= $grossPrice . ' ' . esc_html__('brutto', 'justb2b');
        }

        $output .= '</span>';

        return $output;
    }

    public function handleCartTotals(WC_Cart $cart): void
    {
        if (is_admin() && !defined('DOING_AJAX')) {
            return;
        }

        foreach ($cart->get_cart() as $cart_item) {
            $product = $cart_item['data'];
            $productId = $product->get_id();
            $quantity = $cart_item['quantity'];

            $productModel = new ProductModel($productId, $quantity);
            if (!$productModel->isSimpleProduct()) {
                continue;
            }

            $rule = $productModel->getFirstFullFitRule();
            if (!$rule) {
                continue;
            }

            $priceCalculator = $productModel->getPriceCalculator();
            $baseGross = $priceCalculator->getBaseGrossPrice();
            $finalGross = $priceCalculator->getFinalGrossPrice();

            if ($baseGross > $finalGross) {
                $product->set_regular_price($baseGross);
            }

            $product->set_price($finalGross);
            $product->set_sale_price($finalGross);
        }
    }

    public function getFieldsDefinition(): array
    {
        return [
            (new SelectField('mini_cart_net_price', 'Mini cart net price visibility'))
                ->setOptions([
                    'b2x' => 'b2x',
                    'b2b' => 'b2b',
                    'b2c' => 'b2c',
                ])
                ->setHelpText(__('Choose who should see the net price in the mini cart.', 'justb2b'))
                ->setWidth(50),

            (new SelectField('mini_cart_gross_price', 'Mini cart gross price visibility'))
                ->setOptions([
                    'b2x' => 'b2x',
                    'b2b' => 'b2b',
                    'b2c' => 'b2c',
                ])
                ->setHelpText(__('Choose who should see the gross price in the mini cart.', 'justb2b'))
                ->setWidth(50),
        ];
    }
}
