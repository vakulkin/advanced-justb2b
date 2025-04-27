<?php

namespace JustB2b\Controllers;



defined('ABSPATH') || exit;

use Carbon_Fields\Container;
use JustB2b\Models\ProductModel;
use JustB2b\Fields\FieldBuilder;
use JustB2b\Fields\Definitions\ProductsFieldsDefinition;

class ProductsController extends BaseController
{
    protected static string $modelClass = ProductModel::class;

    public function __construct()
    {
        parent::__construct();
        add_filter('woocommerce_get_price_html', [$this, 'woocommerce_get_price_html'], 25, 2);
        add_action('wp_ajax_justb2b_calculate_price', [$this, 'calculatePriceAjaxHandler']);
        add_action('wp_ajax_nopriv_justb2b_calculate_price', [$this, 'calculatePriceAjaxHandler']);
    }

    public function registerFields()
    {
        $definitions = ProductsFieldsDefinition::getMainFileds();
        $fields = FieldBuilder::buildFields($definitions);

        Container::make('post_meta', 'JustB2B')
            ->where('post_type', '=', 'product')
            ->set_context('side')
            ->set_priority('default')
            ->add_fields($fields);
    }

    public function woocommerce_get_price_html($price_html, $product)
    {
        global $woocommerce_loop;

        if ((is_admin() || defined('DOING_AJAX'))) {
            return $price_html;
        }
        
        $productModel = new ProductModel($product->get_id(), 1);
        
        if (empty($productModel->getFirstFullFitRule())) {
            return $price_html;
        }

        $priceDisplay = $productModel->getPriceDisplay();

        if (isset($woocommerce_loop['name'])) {
            $children = $product->get_children();
            if (!empty($children)) {
                $firstChildId = $children[0];
                return "loop varible first child price";
            }

            return $pricesHtml = $priceDisplay->getPrices(true);
        }

        if ($product->is_type('variable')) {
            return $price_html;
        }

        $pricesHtml = $priceDisplay->getPrices();
        $qtyTable = $priceDisplay->getQtyTable();
        $b2cHtml = $priceDisplay->getB2cHtml();

        $productId = $productModel->getID();
        return <<<HTML
            <div class="justb2b_product" data-product_id="{$productId}">{$pricesHtml}</div>
            {$qtyTable}
            {$b2cHtml}
        HTML;
    }

    public function calculatePriceAjaxHandler(): void
    {
        check_ajax_referer('justb2b_price_nonce', 'nonce');

        $productId = intval($_POST['product_id']);
        $quantity = isset($_POST['qty']) ? intval($_POST['qty']) : 1;

        if (!$productId || !$quantity) {
            wp_send_json_error(['message' => 'Invalid data']);
        }

        $productModel = new ProductModel(
            $productId,
            $quantity
        );

        if (!$productModel) {
            wp_send_json_error(['message' => 'Invalid product']);
        }

        $priceDisplay = $productModel->getPriceDisplay();
        wp_send_json_success([
            'price' => $priceDisplay->getPrices(),
        ]);
    }
}
