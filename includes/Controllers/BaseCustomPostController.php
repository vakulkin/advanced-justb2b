<?php

namespace JustB2b\Controllers;

use RuntimeException;

defined('ABSPATH') || exit;

use JustB2b\Traits\SingletonTrait;

abstract class BaseCustomPostController extends BaseController
{
    use SingletonTrait;

    protected static string $modelClass;

    protected function __construct()
    {
        parent::__construct();

        if (!isset(static::$modelClass)) {
            throw new RuntimeException('Model class not defined in ' . static::class);
        }

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
                'name' => sprintf(__('%s', 'justb2b'), $pluralName),
                'singular_name' => sprintf(__('%s', 'justb2b'), $singleName),
                'add_new' => sprintf(__('Add New %s', 'justb2b'), $singleName),
                'add_new_item' => sprintf(__('Add New %s', 'justb2b'), $singleName),
                'edit_item' => sprintf(__('Edit %s', 'justb2b'), $singleName),
                'new_item' => sprintf(__('New %s', 'justb2b'), $singleName),
                'view_item' => sprintf(__('View %s', 'justb2b'), $singleName),
                'search_items' => sprintf(__('Search %s', 'justb2b'), $pluralName),
                'not_found' => sprintf(__('No %s found', 'justb2b'), strtolower($pluralName)),
                'not_found_in_trash' => sprintf(__('No %s found in Trash', 'justb2b'), strtolower($pluralName)),
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