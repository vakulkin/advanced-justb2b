<?php

namespace JustB2b\Controllers;

use WC_Cart;
use JustB2b\Fields\FieldBuilder;
use JustB2b\Fields\SelectField;
use JustB2b\Utils\Prefixer;
use JustB2b\Traits\SingletonTrait;
use JustB2b\Traits\LazyLoaderTrait;
use JustB2b\Models\ProductModel;

defined('ABSPATH') || exit;

class CartController extends BaseController
{
    use SingletonTrait;
    use LazyLoaderTrait;

    protected ?string $showNetFor = null;
    protected ?string $showGrossFor = null;

    protected function __construct()
    {
        parent::__construct();

        add_filter('woocommerce_widget_cart_item_quantity', [$this, 'miniCartPriceFilter'], 10, 2);
        add_action('woocommerce_before_calculate_totals', [$this, 'handleCartTotals'], 20);
    }

    public function registerCarbonFields()
    {
        $definitions = self::getCartFields();
        $fields = FieldBuilder::buildFields($definitions);

        $globalController = GlobalController::getInstance();
        $generalSettings = $globalController->getGlobalSettings();

        $generalSettings->add_tab('Cart', $fields);
    }

    public static function getCartFields(): array
    {
        return [
            (new SelectField('mini_cart_net_price', 'Mini cart net price visibility'))
                ->setOptions([
                    'b2x' => 'b2x',
                    'b2b' => 'b2b',
                    'b2c' => 'b2c',
                ])
                ->setWidth(50),

            (new SelectField('mini_cart_gross_price', 'Mini cart gross price visibility'))
                ->setOptions([
                    'b2x' => 'b2x',
                    'b2b' => 'b2b',
                    'b2c' => 'b2c',
                ])
                ->setWidth(50),
        ];

    }

    protected function initShowNetFor(): void
    {
        $this->lazyLoad($this->showNetFor, function () {
            return get_option(Prefixer::getPrefixedMeta('mini_cart_net_price')) ?: 'b2x';
        });
    }

    protected function initShowGrossFor(): void
    {
        $this->lazyLoad($this->showGrossFor, function () {
            return get_option(Prefixer::getPrefixedMeta('mini_cart_gross_price')) ?: 'b2x';
        });
    }

    public function getShowNetFor(): string
    {
        $this->initShowNetFor();
        return $this->showNetFor;
    }

    public function getShowGrossFor(): string
    {
        $this->initShowGrossFor();
        return $this->showGrossFor;
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

        $output = '<span class="quantity">' . esc_html($cart_item['quantity']);

        if ($this->getShowNetFor() === $userKind || $this->getShowNetFor() === 'b2x') {
            $output .= ' &times; ' . $net_price_formatted . ' ' . esc_html__('netto', 'justb2b');
        }

        if ($this->getShowGrossFor() === $userKind || $this->getShowGrossFor() === 'b2x') {
            $output .= '<br />' . $gross_price_formatted . ' ' . esc_html__('brutto', 'justb2b');
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
