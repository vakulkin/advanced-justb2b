<?php

namespace JustB2b\Controllers\Id;

use WP_Post;
use WP_Query;
use WC_Product;
use JustB2b\Models\Id\ProductModel;

defined( 'ABSPATH' ) || exit;

/**
 * @feature-section product_visibility
 * @title[ru] Управление отображением и ценами товаров
 * @desc[ru] JustB2B позволяет показывать каждому клиенту именно те товары и цены, которые вы хотите. Цены обновляются автоматически.
 * @order 300
 */

/**
 * @feature product_visibility controller
 * @title[ru] Управление товарами без кода
 * @desc[ru] Плагин сам управляет отображением товаров, ценами, покупаемостью и видимостью в каталоге в зависимости от условий — вы просто задаёте правила.
 * @order 301
 */

class ProductsController extends AbstractPostController {
	protected function __construct() {
		parent::__construct();

		add_filter( 'woocommerce_get_price_html', [ $this, 'filterGetPriceHtml' ], 25, 2 );
		add_action( 'wp_ajax_justb2b_calculate_price', [ $this, 'calculatePriceAjaxHandler' ] );
		add_action( 'wp_ajax_nopriv_justb2b_calculate_price', [ $this, 'calculatePriceAjaxHandler' ] );
		add_action( 'template_redirect', [ $this, 'redirectIfFullyHiddenProduct' ] );
		add_filter( 'woocommerce_product_get_price', [ $this, 'filterZeroPriceRequest' ], 20, 2 );
		add_filter( 'woocommerce_is_purchasable', [ $this, 'filterIsPurchasable' ], 20, 2 );
		add_filter( 'acf/fields/relationship/query/name=justb2b_products', [ $this, 'acfFilterVariationsParentProducts' ], 1000, 3 );
		add_filter( 'acf/fields/relationship/query/name=justb2b_excluding_products', [ $this, 'acfFilterVariationsParentProducts' ], 1000, 3 );

		// Future enhancements:
		// add_action('woocommerce_product_query', [$this, 'hideProductsFromLoop']);
		// add_filter('woocommerce_show_variation_price', '__return_true', 25);
	}

	public function acfFilterVariationsParentProducts( $args, $field, $post_id ) {
		$args['tax_query'][] = [ 
			'taxonomy' => 'product_type',
			'field' => 'slug',
			'terms' => [ 'variable' ],
			'operator' => 'NOT IN',
		];
		
		$args['lang'] = '';
		return $args;
	}

	public static function getKey() {
		return 'product';
	}

	public static function getSingleName(): string {
		return 'Product';
	}

	public static function getPluralName(): string {
		return 'Products';
	}

	public static function getPrefixedKey(): string {
		return static::getKey();
	}

	public function getDefinitions(): array {
		return ProductModel::getKeyFieldsDefinition();
	}

	/**
	 * @feature product_visibility dynamic_price_display
	 * @title[ru] Цены, которые меняются в реальном времени
	 * @desc[ru] JustB2B подменяет цены прямо на витрине WooCommerce в зависимости от роли пользователя, количества, правил и условий — без перезагрузки страницы.
	 * @order 310
	 */
	public function filterGetPriceHtml( $price_html, $product ) {
		if ( is_admin() ) {
			return $price_html;
		}

		global $post, $woocommerce_loop;

		$isMainProduct = is_product()
			&& is_singular( 'product' )
			&& isset( $post )
			&& $product->get_id() === $post->ID;

		$isInNamedLoop = isset( $woocommerce_loop['name'] ) && ! empty( $woocommerce_loop['name'] );
		$isShortcode = isset( $woocommerce_loop['is_shortcode'] ) && $woocommerce_loop['is_shortcode'];
		$isInLoop = $isInNamedLoop || $isShortcode || ! $isMainProduct;

		$productModel = new ProductModel( $product->get_id(), 1 );
		$priceDisplay = $productModel->getPriceDisplay( $price_html, $isInLoop );

		return $priceDisplay->renderPricesHtml();
	}

	/**
	 * @feature product_visibility ajax_price_update
	 * @title[ru] Мгновенное обновление цены при изменении количества
	 * @desc[ru] При смене количества товарной позиции цена пересчитывается моментально с помощью AJAX — клиент сразу видит свою цену.
	 * @order 320
	 */

	public function calculatePriceAjaxHandler(): void {
		check_ajax_referer( 'justb2b_price_nonce', 'nonce' );

		$productId = intval( $_POST['product_id'] );
		$quantity = isset( $_POST['qty'] ) ? intval( $_POST['qty'] ) : 1;

		if ( ! $productId || ! $quantity ) {
			wp_send_json_error( [ 'message' => 'Invalid data' ] );
		}

		$productModel = new ProductModel( $productId, $quantity );

		if ( ! $productModel ) {
			wp_send_json_error( [ 'message' => 'Invalid product' ] );
		}

		$defaultPriceHtml = $productModel->getWCProduct()->get_price_html();
		$priceDisplay = $productModel->getPriceDisplay( $defaultPriceHtml, false );

		wp_send_json_success( [ 
			'price' => $priceDisplay->renderPricesHtml(),
		] );
	}

	/**
	 * @feature product_visibility hide_from_catalog
	 * @title[ru] Скрытие товаров из каталога
	 * @desc[ru] Вы можете полностью скрыть определённые товары из каталога, виджетов и витрин — они не будут видны неподходящим клиентам.
	 * @order 380
	 */

	public function hideProductsFromLoop( WP_Query $q ) {
		$ids_to_exclude = [];

		$loop_query = new WP_Query( [ 
			'post_type' => 'product',
			'post_status' => 'publish',
			'posts_per_page' => -1,
			'fields' => 'ids',
		] );

		foreach ( $loop_query->posts as $product_id ) {
			$productModel = new ProductModel( $product_id, 1 );
			$rule = $productModel->getFirstFullFitRule();
			if ( $rule && $rule->isInLoopHidden() ) {
				$ids_to_exclude[] = $product_id;
			}
		}

		if ( ! empty( $ids_to_exclude ) ) {
			$existing = $q->get( 'post__not_in' ) ?? [];
			$q->set( 'post__not_in', array_merge( $existing, $ids_to_exclude ) );
		}
	}

	/**
	 * @feature product_visibility full_hiding
	 * @title[ru] Скрытие товаров полностью
	 * @desc[ru] Если правило говорит «не показывать» — клиент даже не сможет открыть страницу товара. Абсолютный контроль над тем, кто что видит.
	 * @order 330
	 */

	public function redirectIfFullyHiddenProduct(): void {
		global $post;

		if ( ! $post instanceof WP_Post || ! is_singular( 'product' ) ) {
			return;
		}

		$productModel = new ProductModel( $post->ID, 1 );
		$rule = $productModel->getFirstFullFitRule();

		if ( $rule && $rule->isFullyHidden() ) {
			global $wp_query;
			$wp_query->set_404();
			status_header( 404 );
			nocache_headers();
			include get_query_template( '404' );
			exit;
		}
	}

	/**
	 * @feature product_visibility request_price_mode
	 * @title[ru] Запрос цены вместо цифры
	 * @desc[ru] Вы можете скрыть цену товара и заменить её надписью «Цена по запросу» — например, для эксклюзивных товаров или оптовых клиентов.
	 * @order 370
	 */
	public function filterZeroPriceRequest( $price, $product ) {
		if ( is_admin() ) {
			return $price;
		}

		$productModel = new ProductModel( $product->get_id(), 1 );
		$rule = $productModel->getFirstFullFitRule();

		if ( $rule && $rule->isZeroRequestPrice() ) {
			return 0;
		}

		return $price;
	}

	/**
	 * @feature product_visibility full_hiding
	 * @title[ru] Скрытие товаров полностью
	 * @desc[ru] Если правило говорит «не показывать» — клиент даже не сможет открыть страницу товара. Абсолютный контроль над тем, кто что видит.
	 * @order 330
	 */

	public function filterIsPurchasable( bool $purchasable, WC_Product $product ): bool {
		$productModel = new ProductModel( $product->get_id(), 1 );
		$rule = $productModel->getFirstFullFitRule();
		return ( ! $rule && $purchasable ) || ( $rule && $rule->isPurchasable() );
	}

	// public function carbonFieldsFilterTerms( $query_arguments ) {
	// 	$query_arguments['justb2b_terms_association'] = true;
	// 	return $query_arguments;
	// }
}
