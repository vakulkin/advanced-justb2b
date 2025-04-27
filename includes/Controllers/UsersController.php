<?php

namespace JustB2b\Controllers;

defined('ABSPATH') || exit;

use Carbon_Fields\Container;
use JustB2b\Models\UserModel;
use JustB2b\Fields\FieldBuilder;
use JustB2b\Fields\Definitions\UserFieldsDefinition;
use JustB2b\Traits\LazyLoaderTrait;

class UsersController extends BaseController
{
    use LazyLoaderTrait;

    protected ?UserModel $currentUser = null;

    public function __construct()
    {
        parent::__construct();
    }

    public function registerFields()
    {
        $definitions = UserFieldsDefinition::getMainFileds();
        $fields = FieldBuilder::buildFields($definitions);

        Container::make('user_meta', 'JustB2B')
            ->add_fields($fields);
    }

    protected function initCurrentUser(): void {
        $this->lazyLoad($this->currentUser, function () {
            return new UserModel(get_current_user_id());
        });
    }
   
    public function getCurrentUser(): UserModel
    {   
        $this->initCurrentUser();
        return $this->currentUser;
    }
}
