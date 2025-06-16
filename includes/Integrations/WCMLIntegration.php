<?php

namespace JustB2b\Integrations;

use JustB2b\Fields\NonNegativeFloatField;
use JustB2b\Fields\SelectField;
use JustB2b\Traits\SingletonTrait;

defined('ABSPATH') || exit;

class WCMLIntegration
{
    use SingletonTrait;

    protected function __construct()
    {
        add_filter('wcml_multi_currency_ajax_actions', function ($actions) {
            $actions[] = 'justb2b_calculate_price';
            return $actions;
        });

        add_action('add_meta_boxes', function () {
            $default_lang = apply_filters('wpml_default_language', null);
            $current_lang = apply_filters('wpml_current_language', null);

            if ($default_lang === $current_lang) {
                return;
            }

            remove_meta_box('carbon_fields_container_products', 'product', 'side');
        }, 100);

        add_filter("justb2b_product_fields_definition", [ $this, 'getProductFieldsDefinition' ], 10, 2);
        add_filter("settingsFieldsDefinition", [ $this, 'settingsFieldsDefinition' ], 10, 2);

        add_filter('posts_clauses', function ($clauses, $query) {
            global $pagenow;
            if (
                is_admin() &&
                $pagenow === 'post.php' &&
                $query->get('justb2b_products_association')
            ) {
                $clauses['where'] = preg_replace(
                    '~AND \( \( \( wpml_translations\.language_code~',
                    "AND ( ( ( 1=1 OR wpml_translations.language_code",
                    $clauses['where']
                );
            }
            return $clauses;
        }, 20, 2);


        add_filter('terms_clauses', function ($clauses, $taxonomies, $args) {
            global $pagenow;
            if (
                is_admin() &&
                $pagenow === 'post.php' &&
                isset($args['justb2b_terms_association'])
            ) {
                $clauses['where'] = preg_replace(
                    '~AND \( icl_t\.language_code~',
                    "AND (1=1 OR icl_t.language_code",
                    $clauses['where']
                );
            }
            return $clauses;
        }, 20, 3);


        add_filter('justb2b_check_product', [ $this, 'checkProduct' ], 10, 3);
        add_filter('justb2b_check_terms', [ $this, 'checkTerms' ], 10, 3);
    }

    public function getProductFieldsDefinition(array $fields, array $base_keys)
    {
        global $woocommerce_wpml;

        // Multi-currency fields
        if (
            isset($woocommerce_wpml->settings['currency_options']) &&
            is_array($woocommerce_wpml->settings['currency_options'])
        ) {
            $currency_codes = array_keys($woocommerce_wpml->settings['currency_options']);

            foreach ($currency_codes as $currency) {
                foreach ($base_keys as $key) {
                    $composite_key = strtolower($currency) . '__' . $key;
                    $fields[] = new NonNegativeFloatField($composite_key, $composite_key);
                }
            }
        }
        return $fields;
    }


    public static function currencyWPMLSelectField(): SelectField
    {
        $default_currency = get_option('woocommerce_currency', 'PLN');
        $currencies = [ $default_currency ];

        global $woocommerce_wpml;
        if (
            isset($woocommerce_wpml->settings['currency_options']) &&
            is_array($woocommerce_wpml->settings['currency_options'])
        ) {
            $wpml_currencies = array_keys($woocommerce_wpml->settings['currency_options']);
            $currencies = array_unique(array_merge([ $default_currency ], $wpml_currencies));
        }

        return (new SelectField('currency', 'Currency'))
            ->setOptions(array_combine($currencies, $currencies))
            ->setHelpText('Currency for this rule. Used only for base prices.')
            ->setWidth(25);
    }

    public function checkProduct(bool $result, $products, int $product_id): bool
    {
        $trid = apply_filters('wpml_element_trid', null, $product_id, 'post_product');
        $translations = apply_filters('wpml_get_element_translations', null, $trid, 'post_product') ?: [];
        foreach ($translations as $translation) {
            if (isset($products[ $translation->element_id ])) {
                return true;
            }
        }
        return $result;
    }

    public function checkTerms(bool $result, int $product_id, array $terms): bool
    {
        // Step 2: Get all translated products
        $productTrid = apply_filters('wpml_element_trid', null, $product_id, 'post_product');
        $productTranslations = apply_filters('wpml_get_element_translations', null, $productTrid, 'post_product') ?: [];

        // Step 3+4: Inline term translation + product-term check
        foreach ($terms as $term) {
            $termTrid = apply_filters('wpml_element_trid', null, $term['id'], 'tax_' . $term['taxonomy']);
            $termTranslations = apply_filters('wpml_get_element_translations', null, $termTrid, 'tax_' . $term['taxonomy']) ?: [];

            foreach ($termTranslations as $translatedTerm) {
                $translatedTermId = $translatedTerm->element_id;
                foreach ($productTranslations as $translation) {
                    if (has_term($translatedTermId, $term['taxonomy'], $translation->element_id)) {
                        return true;
                    }
                }
            }
        }
        return $result;
    }

    public function settingsFieldsDefinition(array $fieldsDefinition, array $base_fields)
    {
        global $woocommerce_wpml;
        if (
            isset($woocommerce_wpml->settings['currency_options']) &&
            is_array($woocommerce_wpml->settings['currency_options'])
        ) {
            $currency_codes = array_keys($woocommerce_wpml->settings['currency_options']);

            foreach ($currency_codes as $currency) {
                $currency = strtolower($currency);
                foreach ($base_fields as $field) {
                    $key = "{$currency}__{$field['key']}";
                    $label = "{$field['label']} ({$currency})";
                    $fieldsDefinition[] = (new SelectField($key, $label))
                        ->setOptions([ 'net' => 'net', 'gross' => 'gross' ])
                        ->setWidth(50);
                }
            }
        }
        return $fieldsDefinition;
    }
}
