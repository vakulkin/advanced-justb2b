<?php

namespace JustB2b\Controllers\Id;

defined('ABSPATH') || exit;

use Carbon_Fields\Container;
Use JustB2b\Controllers\AbstractController;
use JustB2b\Models\Id\UserModel;
use JustB2b\Fields\FieldBuilder;
use JustB2b\Traits\RuntimeCacheTrait;

class UsersController extends AbstractController
{
    use RuntimeCacheTrait;

    public function registerCarbonFields()
    {
        $definitions = UserModel::getFieldsDefinition();
        $fields = FieldBuilder::buildFields($definitions);

        Container::make('user_meta', 'JustB2B')
            ->add_fields($fields);
    }

    public function getCurrentUser(): UserModel
    {
        return self::getFromRuntimeCache(function () {
            return new UserModel(get_current_user_id());
        }, ['user_id' => get_current_user_id()]);
    }
}
