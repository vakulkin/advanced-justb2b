<?php

namespace JustB2b\Models\Key;

use JustB2b\Controllers\Key\GlobalController;
use JustB2b\Fields\NonNegativeIntegerField;
use JustB2b\Fields\RichTextField;
use JustB2b\Fields\SelectField;
use JustB2b\Fields\SeparatorField;
use JustB2b\Fields\TextField;

defined( 'ABSPATH' ) || exit;

/**
 * @feature-section settings_ui
 * @title[ru] Отображение цен для B2B и B2C
 * @desc[ru] Управление выводом цен, таблицы правил и HTML-блоков по типу пользователя и контексту.
 * @order 500
 */

/**
 * @feature-section settings_ui
 * @title[ru] Настройки отображения цен и шаблонов
 * @desc[ru] Управление выводом цен, шаблонов и HTML-блоков без правки кода.
 * @order 600
 */

/**
 * @feature settings_ui net_gross_selector
 * @title[ru] Настройка формата цен (нетто / брутто)
 * @desc[ru] Выбор формата расчёта базовой цены — с НДС или без, отдельно для каждой группы цен.
 * @order 601
 */

/**
 * @feature settings_ui price_visibility
 * @title[ru] Отображение цен
 * @desc[ru] Включение или скрытие каждой цены отдельно для B2B и B2C.
 * @order 602
 */

/**
 * @feature settings_ui price_format
 * @title[ru] Префиксы и постфиксы
 * @desc[ru] Добавление префиксов и постфиксов к каждой цене.
 * @order 603
 */

/**
 * @feature settings_ui price_context
 * @title[ru] Контекстное отображение цен
 * @desc[ru] Настройка отображения цен на страницах товаров и в списках — отдельно для B2B и B2C.
 * @order 604
 */

/**
 * @feature settings_ui price_options_count
 * @title[ru] Количество доступных опций
 * @desc[ru] Более 250 параметров настройки отображения цен.
 * @order 605
 */


/**
 * @feature settings_ui custom_html_blocks
 * @title[ru] Кастомный HTML под ценами
 * @desc[ru] Вывод HTML-блоков под ценами — отдельно для B2B и B2C.
 * @order 630
 */

/**
 * @feature settings_ui quantity_table
 * @title[ru] Таблица цен по количеству
 * @desc[ru] Показывает таблицу правил с приоритетами, границами и ценами. Можно скрыть или показать отдельно для B2B и B2C.
 * @order 530
 */



class SettingsModel extends AbstractKeyModel {

	protected function getSettingsId(): int {
		return GlobalController::getSettingsId();
	}

	public static function getFieldsDefinition(): array {

		$fieldsDefinition = [];

		$base_fields = [ 
			[ 'key' => 'base_price_1', 'label' => 'Base price 1' ],
			[ 'key' => 'base_price_2', 'label' => 'Base price 2' ],
			[ 'key' => 'base_price_3', 'label' => 'Base price 3' ],
			[ 'key' => 'base_price_4', 'label' => 'Base price 4' ],
			[ 'key' => 'base_price_5', 'label' => 'Base price 5' ],
			[ 'key' => 'rrp_price', 'label' => 'RRP' ],
		];

		// Default fields (without currency)
		foreach ( $base_fields as $field ) {
			$fieldsDefinition[] = ( new TextField( "setting_label_{$field['key']}", $field['label'] . ' Label' ) )
				->setWidth( 25 );
			$fieldsDefinition[] = ( new SelectField( "setting_type_{$field['key']}", $field['label'] . ' Type' ) )
				->setOptions( [ 'net' => 'net', 'gross' => 'gross' ] )
				->setWidth( 25 );
		}

		$fieldsDefinition = apply_filters( 'justb2b_settings_fields_definition', $fieldsDefinition, $base_fields );

		// Visibility and prefix/postfix configuration
		foreach ( [ [ 'key' => 'base_net', 'label' => 'Base Net' ], [ 'key' => 'base_gross', 'label' => 'Base Gross' ], [ 'key' => 'your_net', 'label' => 'Your Net' ], [ 'key' => 'your_gross', 'label' => 'Your Gross' ], [ 'key' => 'your_net_total', 'label' => 'Your Net Total' ], [ 'key' => 'your_gross_total', 'label' => 'Your Gross Total' ], [ 'key' => 'gifts_net_total', 'label' => 'gifts_net_total' ], [ 'key' => 'gifts_gross_total', 'label' => 'gifts_gross_total' ], [ 'key' => 'final_net_total', 'label' => 'final_net_total' ], [ 'key' => 'final_gross_total', 'label' => 'final_gross_total' ], [ 'key' => 'final_per_item_net', 'label' => 'final_per_item_net' ], [ 'key' => 'final_per_item_gross', 'label' => 'final_per_item_gross' ], [ 'key' => 'rrp_net', 'label' => 'RRP Net' ], [ 'key' => 'rrp_gross', 'label' => 'RRP Gross' ], [ 'key' => 'qty_table', 'label' => 'Qty Table' ],] as $field ) {
			$fieldsDefinition[] = new SeparatorField( "setting_sep_{$field['key']}", $field['label'] );
			$fieldsDefinition = array_merge(
				$fieldsDefinition,
				self::generateVisibilityFields( $field['key'] )
			);
		}

		// Custom HTML fields
		foreach ( [ 'b2c', 'b2b' ] as $type ) {
			$fieldsDefinition[] = ( new SelectField( "setting_show_{$type}_html_1", "show_{$type}_html_1" ) )
				->setOptions( [ 'show' => 'show', 'hide' => 'hide' ] )
				->setWidth( 100 );

			$fieldsDefinition[] = ( new RichTextField( "setting_{$type}_html_1", "{$type}_html_1" ) )->setWidth( 100 );
		}

		return $fieldsDefinition;
	}

	/**
	 * Generates all visibility-related fields for a given key.
	 */
	private static function generateVisibilityFields( string $key ): array {
		$fields = [];

		foreach ( [ 'single', 'loop' ] as $place ) {
			foreach ( [ 'b2c', 'b2b' ] as $kind ) {
				$generalKey = "setting_{$place}_{$kind}_{$key}";
				$visibilityKey = "{$generalKey}_visibility";
				$fields[] = ( new SelectField( $visibilityKey, "{$kind} {$place} visibility" ) )
					->setOptions( [ 
						'show' => 'show',
						'hide' => 'hide',
					] )
					->setWidth( 25 );
				$typePriorityKey = "{$generalKey}_priority";
				$fields[] = ( new NonNegativeIntegerField( $typePriorityKey, "{$kind} display priority" ) )
					->setWidth( 25 );
				foreach ( [ 'prefix', 'postfix' ] as $position ) {
					$finalKey = "{$generalKey}_{$position}";
					$fields[] = ( new TextField( $finalKey, "{$kind} {$place} {$position}" ) )->setWidth( 25 );
				}
			}
		}

		return $fields;
	}
}
