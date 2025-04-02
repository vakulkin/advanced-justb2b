<?php

namespace JustB2b\Controllers;


defined('ABSPATH') || exit;

use Carbon_Fields\Container;
use JustB2b\Models\LogicBlockModel;
use JustB2b\Fields\FieldBuilder;
use JustB2b\Fields\Definitions\LogicBlocksFieldsDefinition;


class LogicBlocksController extends BaseCustomController
{
    protected static string $modelClass = LogicBlockModel::class;

    public function registerFields()
    {
        // $definitions = LogicBlocksFieldsDefinition::getMainFileds();
        // $fields = FieldBuilder::buildFields($definitions);


        // Container::make('post_meta', 'JustB2B')
        //     ->where('post_type', '=', self::$modelClass::getPrefixedKey())
        //     ->set_context('side')
        //     ->set_priority('default')
        //     ->add_fields($fields);


        $definitions = LogicBlocksFieldsDefinition::getMainConditions();
        $fields = FieldBuilder::buildFields($definitions);

        Container::make('post_meta', 'JustB2B')
            ->where('post_type', '=', self::$modelClass::getPrefixedKey())
            ->add_fields($fields);
    }
}
