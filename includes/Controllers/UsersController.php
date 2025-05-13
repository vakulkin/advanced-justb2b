<?php

namespace JustB2b\Controllers;

defined('ABSPATH') || exit;

use Carbon_Fields\Container;
use JustB2b\Models\UserModel;
use JustB2b\Fields\FieldBuilder;
use JustB2b\Traits\RuntimeCacheTrait;

class UsersController extends AbstractController
{
    use RuntimeCacheTrait;

    protected string $modelClass = UserModel::class;

    public function registerCarbonFields()
    {
        $definitions = $this->modelClass::getFieldsDefinition();
        $fields = FieldBuilder::buildFields($definitions);

        Container::make('user_meta', 'JustB2B')
            ->add_fields($fields);
    }

    public function getCurrentUser(): UserModel
    {
        return $this->getFromRuntimeCache('current_user_model', function () {
            return new $this->modelClass(get_current_user_id());
        });
    }
}
