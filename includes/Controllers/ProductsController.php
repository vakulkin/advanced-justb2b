<?php

namespace JustB2b\Controllers;



defined('ABSPATH') || exit;

use Carbon_Fields\Container;
use JustB2b\Models\ProductModel;
use JustB2b\Fields\FieldBuilder;
use JustB2b\Fields\Definitions\ProductsFieldsDefinition;


class ProductsController extends BaseController
{
    public function __construct() {
        parent::__construct();
        add_filter('woocommerce_get_price_html', [$this, 'woocommerce_get_price_html'], 25, 2);
    }

    protected static string $modelClass = ProductModel::class;

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

    public function woocommerce_get_price_html($price_html, $product) {

        global $woocommerce_loop;

        if ((is_admin() || defined('DOING_AJAX'))) {
            return $price_html;
        }

        $productModel = new ProductModel($product->get_id());
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

        return <<<HTML
            <div class="justb2b_product_price_container" data-product_id="">{$pricesHtml}</div>
        HTML;

    }
}
