<?php

namespace JustB2b\Controllers\Id;

use JustB2b\Models\Id\ProductModel;
use JustB2b\Models\Id\RuleModel;
use JustB2b\Fields\AbstractField;
use WP_Query;

defined('ABSPATH') || exit;

/**
 * @feature-section admin_rules
 * @title[ru] Удобное управление ценовыми правилами
 * @desc[ru] Вы можете легко создавать и редактировать правила, которые автоматически изменяют цену в зависимости от роли клиента, количества, категории и других условий — без необходимости в коде.
 * @order 200
 */

/**
 * @feature admin_rules controller
 * @title[ru] Всё под контролем — в одном месте
 * @desc[ru] Все ценовые правила собраны в удобной таблице в админке: редактируйте, фильтруйте, сортируйте.
 * @order 201
 */

class RulesController extends AbstractCustomPostController
{
    protected function __construct()
    {
        parent::__construct();
        $this->registerAdminColumns();
        add_filter('acf/fields/relationship/result', [ $this, 'addImageRelation' ], 10, 4);

        add_action('admin_head', function () {
            $screen = get_current_screen();
            if ($screen->post_type === $this->getPrefixedKey()) {
                echo '<style>
					html, body {
						overflow-x: hidden !important;
					}

					.wp-list-table.widefat {
						display: block;
						overflow-x: auto;
						white-space: nowrap;
						padding-bottom: 16px;
					}
					.wp-list-table.widefat thead th,
					.wp-list-table.widefat tbody td {
						white-space: nowrap;
					}
				</style>';
            }
        });

        add_filter('acf/prepare_field/name=justb2b_rule_users', [ $this, 'prepareAssocField' ]);
        add_filter('acf/prepare_field/name=justb2b_rule_roles', [ $this, 'prepareAssocField' ]);
        add_filter('acf/prepare_field/name=justb2b_rule_products', [ $this, 'prepareAssocField' ]);
        add_filter('acf/prepare_field/name=justb2b_rule_woo_terms', [ $this, 'prepareAssocField' ]);

        add_filter('acf/prepare_field/name=justb2b_rule_qualifying_roles', [ $this, 'prepareAssocField' ]);
        add_filter('acf/prepare_field/name=justb2b_rule_qualifying_woo_terms', [ $this, 'prepareAssocField' ]);

        add_filter('acf/prepare_field/name=justb2b_rule_excluding_users', [ $this, 'prepareAssocField' ]);
        add_filter('acf/prepare_field/name=justb2b_rule_excluding_roles', [ $this, 'prepareAssocField' ]);
        add_filter('acf/prepare_field/name=justb2b_rule_excluding_products', [ $this, 'prepareAssocField' ]);
        add_filter('acf/prepare_field/name=justb2b_rule_excluding_woo_terms', [ $this, 'prepareAssocField' ]);

        add_action('pre_get_posts', function (WP_Query $query) {
            if (! is_admin() || ! $query->is_main_query()) {
                return;
            }

            $screen = get_current_screen();
            if ($screen && $screen->post_type === static::getPrefixedKey()) {

                $meta_query = array_merge(
                    ProductModel::getPriorityClause(),
                    ProductModel::getMinQtyClause(),
                    ProductModel::getMaxQtyClause()
                );

                $query->set('meta_query', $meta_query);

                $query->set('orderby', [
                    'priority_clause' => 'ASC',
                    'min_qty_clause' => 'ASC',
                    'max_qty_clause' => 'DESC',
                    'ID' => 'ASC',
                ]);
            }
        });
    }

    public static function prepareAssocField(array $field): array
    {
        /** @var AbstractField $fieldObj */

        if (is_admin() && isset($_GET['post']) && isset($_GET['action']) && 'edit' === $_GET['action']) {
            $post_id = (int) $_GET['post'];

            $withoutPrefix = str_replace('justb2b_', '', $field['key']);
            $fieldObj = RuleModel::getField($withoutPrefix);

            $rendered = $fieldObj->renderValue($post_id);

            // Wrap rendered value in a styled <div>
            $field['instructions'] .= '<div style="margin-top:1em;">' . $rendered . '</div>';
        }

        return $field;
    }

    public function addImageRelation($title, $post, $field, $post_id)
    {
        // if ( isset( $field['name'] ) && $field['name'] === 'your_relationship_field_name' ) {
        $image_size = [ 22, 22 ];
        $thumbnail = get_the_post_thumbnail($post->ID, $image_size);
        if ($thumbnail) {
            $title = '<div class="thumbnail">' . $thumbnail . '</div>' . $title;
        }
        return $title;
    }

    public static function getKey()
    {
        return 'rule';
    }

    public static function getSingleName(): string
    {
        return 'Rule';
    }

    public static function getPluralName(): string
    {
        return 'Rules';
    }
    public function getDefinitions(): array
    {
        return RuleModel::getKeyFieldsDefinition();
    }
}
