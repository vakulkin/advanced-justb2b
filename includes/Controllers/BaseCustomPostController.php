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
        $singleName = static::$modelClass::getSingleName();
        $pluralName = static::$modelClass::getPluralName();

        register_post_type(static::$modelClass::getPrefixedKey(), [
            'label' => $singleName,
            'public' => false,
            'show_ui' => true,
            'show_in_menu' => false,
            'capability_type' => 'post',
            'supports' => ['title'],
            'labels' => [
                'name' => $pluralName,
                'singular_name' => $singleName,
                'add_new' => __("Add New {$singleName}", 'justb2b'),
                'add_new_item' => __("Add New {$singleName}", 'justb2b'),
                'edit_item' => __("Edit {$singleName}", 'justb2b'),
                'new_item' => __("New {$singleName}", 'justb2b'),
                'view_item' => __("View {$singleName}", 'justb2b'),
                'search_items' => __("Search {$pluralName}", 'justb2b'),
                'not_found' => __("Not Found {$pluralName}", 'justb2b'),
                'not_found_in_trash' => __("Not Found {$pluralName} in Trash", 'justb2b'),
            ],
        ]);
    }

    public function registerSubmenus()
    {
        $prefixedKey = static::$modelClass::getPrefixedKey();
        add_submenu_page(
            'justb2b-settings',
            static::$modelClass::getPluralName(),
            static::$modelClass::getPluralName(),
            'edit_posts',
            "edit.php?post_type={$prefixedKey}"
        );
    }
}