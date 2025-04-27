<?php

namespace JustB2b\Controllers;

defined('ABSPATH') || exit;

use JustB2b\Traits\SingletonTrait;

abstract class BaseCustomPostController extends BaseController
{
    use SingletonTrait;

    protected static string $modelClass;

    public function __construct()
    {
        parent::__construct();
        add_action('init', [$this, 'registerPostType']);
        add_action('admin_menu', [$this, 'registerSubmenus'], 100);
    }

    public function registerPostType()
    {
        register_post_type(static::$modelClass::getPrefixedKey(), [
            'label' => static::$modelClass::getKey(),
            'public' => false,
            'show_ui' => true,
            'show_in_menu' => false,
            'capability_type' => 'post',
            'supports' => ['title'],
        ]);
    }

    public function registerSubmenus()
    {
        $prefixedKey = static::$modelClass::getPrefixedKey();
        add_submenu_page(
            'justb2b-settings',
            static::$modelClass::getKey(),
            static::$modelClass::getKey(),
            'edit_posts',
            "edit.php?post_type={$prefixedKey}"
        );
    }
}