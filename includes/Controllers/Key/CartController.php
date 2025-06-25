<?php

namespace JustB2b\Controllers\Key;

use WC_Cart;
use JustB2b\Controllers\Key\AbstractKeyController;
use JustB2b\Controllers\Id\UsersController;
use JustB2b\Models\Id\ProductModel;
use JustB2b\Models\Key\CartModel;
use JustB2b\Traits\SingletonTrait;
use JustB2b\Traits\RuntimeCacheTrait;

defined( 'ABSPATH' ) || exit;

class CartController extends AbstractKeyController {
	use SingletonTrait;
	use RuntimeCacheTrait;

	protected CartModel $cartModelObject;
	protected function __construct() {
		parent::__construct();

		$this->cartModelObject = new CartModel();

		add_filter( 'woocommerce_widget_cart_item_quantity', [ $this, 'miniCartPriceFilter' ], 10, 2 );
		add_action( 'woocommerce_before_calculate_totals', [ $this, 'handleCartTotals' ], 20 );
	}

	public static function getKey() {
		return 'cart';
	}

	public static function getSingleName(): string {
		return 'Cart';
	}

	public static function getPluralName(): string {
		return 'Carts';
	}

	public function getDefinitions(): array {
		return CartModel::getKeyFieldsDefinition();
	}

	public function getShowNetFor(): string {
		return $this->cartModelObject->getFieldValue( 'cart_mini_net_price' );
	}

	public function getShowGrossFor(): string {
		return $this->cartModelObject->getFieldValue( 'cart_mini_gross_price' );
	}

	public function miniCartPriceFilter( $output, $cart_item ): string {
		$quantity = $cart_item['quantity'];
		$netPrice = wc_price( $cart_item['line_subtotal'] / $quantity );
		$grossPrice = wc_price( ( $cart_item['line_subtotal'] + $cart_item['line_subtotal_tax'] ) / $quantity );

		$userKind = UsersController::getCurrentUser()->isB2b() ? 'b2b' : 'b2c';
		$showNet = in_array( $this->getShowNetFor(), [ $userKind, 'b2x' ], true );
		$showGross = in_array( $this->getShowGrossFor(), [ $userKind, 'b2x' ], true );
		$output = '<span class="quantity">' . esc_html( $quantity );

		if ( $showNet ) {
			$output .= ' &times; ' . $netPrice . ' ' . esc_html__( 'netto', 'justb2b' );
		}

		if ( $showGross ) {
			$output .= $showNet ? '<br />' : ' &times; ';
			$output .= $grossPrice . ' ' . esc_html__( 'brutto', 'justb2b' );
		}

		$output .= '</span>';
		return $output;
	}

	public function handleCartTotals( WC_Cart $cart ): void {
		if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
			return;
		}

		foreach ( $cart->get_cart() as $cart_item ) {
			$product = $cart_item['data'];
			$productId = $product->get_id();
			$quantity = $cart_item['quantity'];

			$productModel = new ProductModel( $productId, $quantity );
			if ( ! $productModel->isSimpleProduct() ) {
				continue;
			}

			$rule = $productModel->getFirstFullFitRule();
			if ( ! $rule ) {
				continue;
			}

			$priceCalculator = $productModel->getPriceCalculator();
			$baseGross = $priceCalculator->getBaseGrossPrice();
			$finalGross = $priceCalculator->getFinalGrossPerItemPrice();

			if ( $baseGross > $finalGross ) {
				$product->set_regular_price( $baseGross );
			}

			$product->set_price( $finalGross );
			$product->set_sale_price( $finalGross );
		}
	}
}
