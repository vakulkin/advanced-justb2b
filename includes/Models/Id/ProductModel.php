<?php

namespace JustB2b\Models\Id;

use JustB2b\Controllers\Id\SettingsController;
use JustB2b\Controllers\Key\GlobalController;
use WP_Query;
use WC_Product;
use JustB2b\Controllers\Id\UsersController;
use JustB2b\Fields\NonNegativeFloatField;
use JustB2b\Traits\RuntimeCacheTrait;
use JustB2b\Utils\Prefixer;
use JustB2b\Utils\Pricing\PriceCalculator;
use JustB2b\Utils\Pricing\PriceDisplay;

defined( 'ABSPATH' ) || exit;

/**
 * @feature-section product_logic
 * @title[ru] Логика товаров и расчёта цен
 * @desc[ru] JustB2B автоматически подбирает правила и рассчитывает цену для каждого товара с учётом условий.
 * @order 400
 */

/**
 * @feature product_logic model
 * @title[ru] Привязка правил к товарам
 * @desc[ru] Товар может участвовать в нескольких правилах. Применяется подходящее правило.
 * @order 401
 */

/**
 * @feature product_logic rule_matching
 * @title[ru] Подбор правил
 * @desc[ru] Правила подбираются по пользователю, ролям, количеству, категориям и другим условиям.
 * @order 410
 */

/**
 * @feature product_logic rule_priority
 * @title[ru] Приоритет правил
 * @desc[ru] Применяется правило с минимальным значением приоритета, подходящее по условиям.
 * @order 420
 */

/**
 * @feature product_logic price_calculator
 * @title[ru] Расчёт цены
 * @desc[ru] Цена рассчитывается с учётом условий, количества, скидок, наценок и налогов.
 * @order 430
 */

/**
 * @feature product_logic price_display
 * @title[ru] Вывод рассчитанной цены
 * @desc[ru] Показывается только та цена, которая применима к клиенту.
 * @order 440
 */

/**
 * @feature product_logic base_prices
 * @title[ru] Источники базовых цен
 * @desc[ru] Поддержка до 5 базовых цен и RRP. Используются в расчётах и отображении.
 * @order 450
 */


class ProductModel extends AbstractPostModel {
	use RuntimeCacheTrait;
	protected int $qty;
	protected int $originLangProductId;

	private ?RuleModel $cachedFirstFullFitRule = null;

	public function __construct( int $id, int $conditionQty ) {
		parent::__construct( $id );
		$default_language = apply_filters( 'wpml_default_language', null );
		$origin_language_id = apply_filters( 'wpml_object_id', $id, 'product', false, $default_language ) ?: $id;
		$this->originLangProductId = $origin_language_id;
		$this->qty = $conditionQty;
	}

	public function getOriginLangProductId(): int {
		return $this->originLangProductId;
	}

	protected function cacheContext( array $extra = [] ): array {
		return array_merge( [ 
			parent::cacheContext( $extra ),
			'qty' => $this->qty
		] );
	}

	public function getQty(): int {
		return $this->qty;
	}

	public function getWCProduct(): WC_Product {
		return self::getFromRuntimeCache(
			fn() => wc_get_product( $this->id ),
			$this->cacheContext()
		);
	}

	public function isSimpleProduct(): bool {
		return $this->getWCProduct()->is_type( 'simple' );
	}

	public function isVariableProduct(): bool {
		return $this->getWCProduct()->is_type( 'variable' );
	}

	public function isVariation(): bool {
		return $this->getWCProduct()->is_type( 'variation' );
	}

	public function isDifferentTypeProduct(): bool {
		return ! $this->isSimpleProduct() && ! $this->isVariableProduct() && ! $this->isVariation();
	}

	/**
	 * @feature product_logic rule_matching
	 * @title[ru] Автоматический подбор правил
	 * @desc[ru] Плагин находит все правила, подходящие под товар, пользователя, категории, группы и другие условия — вам не нужно ничего связывать вручную.
	 * @order 410
	 */

	public function getProductRules(): array {
		return self::getFromRuntimeCache( function () {
			$query = new WP_Query( $this->getRuleQueryArgs() );

			$results = [];
			foreach ( $query->posts as $post ) {
				$rule = new RuleModel( $post->ID, $this->getId(), $this->getOriginLangProductId(), $this->getQty() );
				if ( $rule->isFullRuleFit() ) {
					$results[] = $rule;
				}
			}
			return $results;
		}, $this->cacheContext() );
	}

	/**
	 * @feature product_logic rule_priority
	 * @title[ru] Приоритет правил
	 * @desc[ru] Если к товару подходит несколько правил, применяется то, что имеет наивысший приоритет и подходит по количеству.
	 * @order 420
	 */

	public function getFirstFullFitRule(): ?RuleModel {
		return self::getFromRuntimeCache( function () {
			foreach ( $this->getProductRules() as $rule ) {
				if ( $rule->doesQtyFits() ) {
					return $rule;
				}
			}
			return null;
		}, $this->cacheContext() );
	}

	/**
	 * @feature product_logic price_calculator
	 * @title[ru] Мгновенный пересчёт цены
	 * @desc[ru] JustB2B рассчитывает цену в зависимости от условий и количества — с учётом скидок, наценок, базовых цен и налогов.
	 * @order 430
	 */

	public function getPriceCalculator(): PriceCalculator {
		return self::getFromRuntimeCache(
			fn() => new PriceCalculator( $this ),
			$this->cacheContext()
		);
	}

	/**
	 * @feature product_logic price_display
	 * @title[ru] Отображение нужной цены нужному клиенту
	 * @desc[ru] Клиент видит именно ту цену, которая для него рассчитана. Больше не нужно догадываться, почему цена отличается.
	 * @order 440
	 */

	public function getPriceDisplay( string $defaultPriceHtml, bool $isInLoop ): PriceDisplay {
		return self::getFromRuntimeCache(
			fn() => new PriceDisplay( $this, $defaultPriceHtml, $isInLoop ),
			$this->cacheContext( [ 'is_loop' => $isInLoop ] )
		);
	}

	public static function getMinQtyClause(): array {
		return [ 
			'min_qty_clause' => [ 
				'key' => Prefixer::getPrefixed( 'rule_min_qty' ),
				'type' => 'NUMERIC',
			],
		];
	}

	public static function getMaxQtyClause(): array {
		return [ 
			'max_qty_clause' => [ 
				'key' => Prefixer::getPrefixed( 'rule_max_qty' ),
				'type' => 'NUMERIC',
			],
		];
	}

	protected function getOrderByClauses(): array {
		return [ 
			'priority_clause' => 'ASC',
			'min_qty_clause' => 'ASC',
			'max_qty_clause' => 'DESC',
			'ID' => 'ASC',
		];
	}

	protected function getRuleQueryArgs(): array {
		$user = UsersController::getCurrentUser();

		$meta = array_merge(
			self::getBaseMetaQuery( $user->isB2b() ),
			self::getMinQtyClause(),
			self::getMaxQtyClause()
		);

		return [ 
			'post_type' => Prefixer::getPrefixed( 'rule' ),
			'post_status' => 'publish',
			'posts_per_page' => -1,
			'meta_query' => $meta,
			'orderby' => $this->getOrderByClauses(),
		];
	}


	public static function getFieldsDefinition(): array {
		$base_keys = [ 
			'base_price_1',
			'base_price_2',
			'base_price_3',
			'base_price_4',
			'base_price_5',
			'rrp_price',
		];

		$settingsController = SettingsController::getInstance();

		$fields = array_map(
			fn( $key ) => ( new NonNegativeFloatField( $key, $settingsController->getField( "setting_label_{$key}" )->getValue(GlobalController::getSettingsId()) ?: $key ) )->setWidth( 33 ),
			$base_keys
		);

		return apply_filters( "justb2b_product_fields_definition", $fields, $base_keys );
	}
}
