<?php

namespace JustB2b\Controllers;


defined('ABSPATH') || exit;

use Carbon_Fields\Container;
use JustB2b\Models\RoleModel;
use JustB2b\Fields\FieldBuilder;
use JustB2b\Fields\Definitions\RolesFieldsDefinition;


class RolesController extends BaseCustomController
{
    protected static string $modelClass = RoleModel::class;

    public function registerFields()
    {
        $definitions = RolesFieldsDefinition::getMainFileds();
        $fields = FieldBuilder::buildFields($definitions);

        Container::make('post_meta', 'JustB2B')
            ->where('post_type', '=', self::$modelClass::getPrefixedKey())
            ->set_context('side')
            ->set_priority('default')
            ->add_fields($fields);


        $definitions = RolesFieldsDefinition::getUsersFields();
        $fields = FieldBuilder::buildFields($definitions);

        Container::make('post_meta', 'JustB2B')
            ->where('post_type', '=', self::$modelClass::getPrefixedKey())
            ->add_fields($fields);
    }
}
