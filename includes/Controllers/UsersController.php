<?php

namespace JustB2b\Controllers;

use JustB2b\Fields\SelectField;
use Carbon_Fields\Container;
use JustB2b\Models\UserModel;
use JustB2b\Fields\FieldBuilder;
use JustB2b\Traits\LazyLoaderTrait;


defined('ABSPATH') || exit;


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
        $definitions = self::getMainFileds();
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

    public static function getMainFileds(): array
    {
        return [
            (new SelectField('kind', 'Rodzaj'))
                ->setOptions([
                    'b2c' => 'b2c',
                    'b2b' => 'b2b',
                ])
        ];
    }
}
