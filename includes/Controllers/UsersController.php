<?php

namespace JustB2b\Controllers;

defined('ABSPATH') || exit;

use Carbon_Fields\Container;
use JustB2b\Fields\FieldBuilder;
use JustB2b\Fields\Definitions\UserFieldsDefinition;


class UsersController extends BaseController
{
    protected static string $modelClass = UserModel::class;

    public function registerFields()
    {
        $definitions = UserFieldsDefinition::getMainFileds();
        $fields = FieldBuilder::buildFields($definitions);

        Container::make('user_meta', 'Address')
            ->add_fields($fields);
    }
}