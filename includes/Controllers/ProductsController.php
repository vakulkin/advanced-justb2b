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
}
