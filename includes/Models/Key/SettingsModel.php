<?php

namespace JustB2b\Models\Key;

use JustB2b\Fields\RichTextField;
use JustB2b\Fields\SelectField;
use JustB2b\Fields\SeparatorField;
use JustB2b\Fields\TextField;

defined('ABSPATH') || exit;

/**
 * @feature-section settings_ui
 * @title[ru] Настройки отображения цен и шаблонов
 * @desc[ru] Гибкие настройки отображения цен, шаблонов и пользовательских HTML-блоков без необходимости править код.
 * @order 600
 */

/**
 * @feature settings_ui net_gross_selector
 * @title[ru] Настройка формата цен (нетто / брутто)
 * @desc[ru] Выберите, в каком формате (с НДС или без) будет рассчитываться базовая цена — для каждой группы цен отдельно.
 * @order 601
 */

/**
 * @feature settings_ui conditional_blocks
 * @title[ru] Видимость блоков цен в каталоге и на карточке
 * @desc[ru] Управляйте, какие блоки цен отображаются и в каком контексте (каталог или карточка товара), отдельно для B2B и B2C.
 * @order 610
 */

/**
 * @feature settings_ui conditional_blocks
 * @title[ru] Видимость блоков цен в каталоге и на карточке
 * @desc[ru] Управляйте, какие блоки цен отображаются и в каком контексте (каталог или карточка товара), отдельно для B2B и B2C.
 * @order 610
 */

/**
 * @feature settings_ui custom_html_blocks
 * @title[ru] Кастомный HTML под ценами
 * @desc[ru] Отображайте любой HTML-блок под ценами (например, условия доставки или скидок) — отдельно для B2B и B2C клиентов.
 * @order 630
 */


class SettingsModel extends AbstractKeyModel
{
    public function getKey(): string
    {
        return 'settings';
    }

    public static function getFieldsDefinition(): array
    {
        $fieldsDefinition = [];

        // Price types: net/gross selector
        foreach ([['key' => 'rrp_price', 'label' => 'RRP'], ['key' => 'base_price_1', 'label' => 'Base price 1'], ['key' => 'base_price_2', 'label' => 'Base price 2'], ['key' => 'base_price_3', 'label' => 'Base price 3'], ['key' => 'base_price_4', 'label' => 'Base price 4'], ['key' => 'base_price_5', 'label' => 'Base price 5'],] as $field) {
            $fieldsDefinition[] = (new SelectField($field['key'], $field['label']))
                ->setOptions(['net' => 'net', 'gross' => 'gross'])
                ->setWidth(50);
        }

        // Visibility and prefix/postfix configuration
        foreach ([['key' => 'base_net', 'label' => 'Base Net'], ['key' => 'base_gross', 'label' => 'Base Gross'], ['key' => 'final_net', 'label' => 'Final Net'], ['key' => 'final_gross', 'label' => 'Final Gross'], ['key' => 'rrp_net', 'label' => 'RRP Net'], ['key' => 'rrp_gross', 'label' => 'RRP Gross'], ['key' => 'qty_table', 'label' => 'Qty Table'],] as $field) {
            $fieldsDefinition[] = new SeparatorField("sep_{$field['key']}", $field['label']);
            $fieldsDefinition = array_merge(
                $fieldsDefinition,
                self::generateVisibilityFields($field['key'])
            );
        }

        // Custom HTML fields
        foreach (['b2c', 'b2b'] as $type) {
            $fieldsDefinition[] = (new SelectField("show_{$type}_html_1", "show_{$type}_html_1"))
                ->setOptions(['show' => 'show', 'hide' => 'hide'])
                ->setWidth(100);

            $fieldsDefinition[] = (new RichTextField("{$type}_html_1", "{$type}_html_1"))->setWidth(100);
        }

        return $fieldsDefinition;
    }

    /**
     * Generates all visibility-related fields for a given key.
     */
    private static function generateVisibilityFields(string $key): array
    {
        $fields = [];

        foreach (['single', 'loop'] as $place) {
            foreach (['b2c', 'b2b'] as $kind) {
                foreach (['prefix', 'postfix'] as $position) {
                    $finalKey = "{$place}_{$kind}_{$key}_{$position}";
                    $fields[] = (new TextField($finalKey, "{$kind} {$place} {$position}"))->setWidth(25);
                }
            }
        }

        foreach (['b2c', 'b2b'] as $kind) {
            $typeKey = "{$kind}_{$key}";
            $fields[] = (new SelectField($typeKey, "{$kind} visibility"))
                ->setOptions([
                    'show' => 'show',
                    'hide' => 'hide',
                    'only_product' => 'only_product',
                    'only_loop' => 'only_loop',
                ])
                ->setWidth(50);
        }

        return $fields;
    }
}
