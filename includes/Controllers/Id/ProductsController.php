<?php

namespace JustB2b\Controllers\Id;

use WP_Post;
use WP_Query;
use WC_Product;
use Carbon_Fields\Container;
use JustB2b\Controllers\AbstractController;
use JustB2b\Models\Id\ProductModel;
use JustB2b\Fields\FieldBuilder;

defined('ABSPATH') || exit;

class ProductsController extends AbstractController
{
    protected string $modelClass = ProductModel::class;

    protected function __construct()
    {
        parent::__construct();

        add_filter('woocommerce_get_price_html', [$this, 'filterGetPriceHtml'], 25, 2);
        add_action('wp_ajax_justb2b_calculate_price', [$this, 'calculatePriceAjaxHandler']);
        add_action('wp_ajax_nopriv_justb2b_calculate_price', [$this, 'calculatePriceAjaxHandler']);
        add_action('template_redirect', [$this, 'redirectIfFullyHiddenProduct']);
        add_filter('woocommerce_product_get_price', [$this, 'filterZeroPriceRequest'], 20, 2);
        add_filter('woocommerce_is_purchasable', [$this, 'filterIsPurchasable'], 20, 2);

        // Future enhancements:
        // add_action('woocommerce_product_query', [$this, 'hideProductsFromLoop']);
        // add_filter('woocommerce_show_variation_price', '__return_true', 25);
        add_filter('carbon_fields_association_field_options_justb2b_products_post_product', [$this, 'carbonFieldsFilterVariationsParentProducts']);
        add_filter('carbon_fields_association_field_options_justb2b_excluding_products_post_product', [$this, 'carbonFieldsFilterVariationsParentProducts']);

        add_filter('carbon_fields_association_field_options_justb2b_woo_terms_term_product_cat', [$this, 'carbonFieldsFilterTerms']);
        add_filter('carbon_fields_association_field_options_justb2b_qualifying_woo_terms_term_product_cat', [$this, 'carbonFieldsFilterTerms']);
        add_filter('carbon_fields_association_field_options_justb2b_excluding_woo_terms_term_product_cat', [$this, 'carbonFieldsFilterTerms']);
        add_filter('carbon_fields_association_field_options_justb2b_woo_terms_term_product_tag', [$this, 'carbonFieldsFilterTerms']);
        add_filter('carbon_fields_association_field_options_justb2b_qualifying_woo_terms_term_product_tag', [$this, 'carbonFieldsFilterTerms']);
        add_filter('carbon_fields_association_field_options_justb2b_excluding_woo_terms_term_product_tag', [$this, 'carbonFieldsFilterTerms']);
    }

    public function force_variation_price_display($variation_data, $product, $variation)
    {
        $variation_data['price_html'] = $variation->get_price_html();
        return $variation_data;
    }

    public function carbonFieldsFilterVariationsParentProducts($query_arguments)
    {
        $query_arguments['tax_query'][] = [
            'taxonomy' => 'product_type',
            'field' => 'slug',
            'terms' => ['variable'],
            'operator' => 'NOT IN',
        ];

        return $query_arguments;
    }

    public function registerCarbonFields()
    {
        $definitions = $this->modelClass::getFieldsDefinition();
        $fields = FieldBuilder::buildFields($definitions);

        Container::make('post_meta', 'JustB2B')
            ->where('post_type', '=', 'product')
            ->set_context('side')
            ->set_priority('default')
            ->add_fields($fields);
    }

    public function filterGetPriceHtml($price_html, $product)
    {
        if (is_admin()) {
            return $price_html;
        }

        $productModel = new $this->modelClass($product->get_id(), 1);

        global $post, $woocommerce_loop;

        $isMainProduct = is_product()
            && is_singular('product')
            && isset($post)
            && $product->get_id() === $post->ID;

        $isInNamedLoop = isset($woocommerce_loop['name']) && !empty($woocommerce_loop['name']);
        $isShortcode = isset($woocommerce_loop['is_shortcode']) && $woocommerce_loop['is_shortcode'];
        $isInLoop = $isInNamedLoop || $isShortcode || !$isMainProduct;

        $priceDisplay = $productModel->getPriceDisplay($price_html, $isInLoop);

        return $priceDisplay->renderPricesHtml();
    }

    public function calculatePriceAjaxHandler(): void
    {
        check_ajax_referer('justb2b_price_nonce', 'nonce');

        $productId = intval($_POST['product_id']);
        $quantity = isset($_POST['qty']) ? intval($_POST['qty']) : 1;

        if (!$productId || !$quantity) {
            wp_send_json_error(['message' => 'Invalid data']);
        }

        $productModel = new $this->modelClass($productId, $quantity);

        if (!$productModel) {
            wp_send_json_error(['message' => 'Invalid product']);
        }

        $defaultPriceHtml = $productModel->getWCProduct()->get_price_html();
        $priceDisplay = $productModel->getPriceDisplay($defaultPriceHtml, false);

        wp_send_json_success([
            'price' => $priceDisplay->renderPricesHtml(),
        ]);
    }

    public function hideProductsFromLoop(WP_Query $q)
    {
        $ids_to_exclude = [];

        $loop_query = new WP_Query([
            'post_type' => 'product',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'fields' => 'ids',
        ]);

        foreach ($loop_query->posts as $product_id) {
            $productModel = new $this->modelClass($product_id, 1);
            $rule = $productModel->getFirstFullFitRule();
            if ($rule && $rule->isInLoopHidden()) {
                $ids_to_exclude[] = $product_id;
            }
        }

        if (!empty($ids_to_exclude)) {
            $existing = $q->get('post__not_in') ?? [];
            $q->set('post__not_in', array_merge($existing, $ids_to_exclude));
        }
    }

    public function redirectIfFullyHiddenProduct(): void
    {
        global $post;

        if (!$post instanceof WP_Post || !is_singular('product')) {
            return;
        }

        $productModel = new $this->modelClass($post->ID, 1);
        $rule = $productModel->getFirstFullFitRule();

        if ($rule && $rule->isFullyHidden()) {
            global $wp_query;
            $wp_query->set_404();
            status_header(404);
            nocache_headers();
            include get_query_template('404');
            exit;
        }
    }

    public function filterZeroPriceRequest($price, $product)
    {
        if (is_admin()) {
            return $price;
        }

        $productModel = new $this->modelClass($product->get_id(), 1);
        $rule = $productModel->getFirstFullFitRule();

        if ($rule && $rule->isZeroRequestPrice()) {
            return 0;
        }

        return $price;
    }

    public function filterIsPurchasable(bool $purchasable, WC_Product $product): bool
    {
        $productModel = new $this->modelClass($product->get_id(), 1);
        $rule = $productModel->getFirstFullFitRule();
        return (!$rule && $purchasable) || ($rule && $rule->isPurchasable());
    }

    public function carbonFieldsFilterTerms($query_arguments)
    {
        if (!isset($query_arguments['orderby'])) {
            $query_arguments['orderby'] = 'name';
        }
        if (!isset($query_arguments['order'])) {
            $query_arguments['order'] = 'ASC';
        }
        return $query_arguments;
    }
}
