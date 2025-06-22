<?php

namespace JustB2b\Utils\Pricing;

use JustB2b\Controllers\Id\UsersController;
use JustB2b\Controllers\Key\GlobalController;
use JustB2b\Models\Id\ProductModel;
use JustB2b\Traits\RuntimeCacheTrait;

defined( 'ABSPATH' ) || exit;

/**
 * @feature-section price_display
 * @title[ru] Отображение цен для B2B и B2C
 * @desc[ru] Управляет отображением цен в зависимости от типа пользователя, правил, контекста (каталог или товар), с поддержкой HTML-шаблонов и таблиц количеств.
 * @order 500
 */
class PriceDisplay {
	use RuntimeCacheTrait;

	protected ProductModel $product;
	protected string $defaultPriceHtml;
	protected bool $isInLoop;

	public function __construct( ProductModel $product, string $defaultPriceHtml, bool $isInLoop ) {
		$this->product = $product;
		$this->defaultPriceHtml = $defaultPriceHtml;
		$this->isInLoop = $isInLoop;
	}

	protected function cacheContext( array $extra = [] ): array {
		return array_merge( [ 
			'product_id' => $this->product->getId(),
			'qty' => $this->product->getQty(),
			'is_loop' => $this->isInLoop,
		], $extra );
	}

	public function getBaseNetPrice(): string {
		return self::getFromRuntimeCache( function () {
			$calculator = $this->product->getPriceCalculator();
			$baseNet = $calculator->getBaseNetPrice();
			$yourNet = $calculator->getYourNetPrice();
			$shouldDisplay = $baseNet > 0 || $baseNet > $yourNet;
			$formatted = $shouldDisplay ? wc_price( $baseNet ) : '';
			return apply_filters( 'justb2b_display_base_net_price', $formatted, $this );
		}, $this->cacheContext() );
	}


	public function getBaseGrossPrice(): string {
		return self::getFromRuntimeCache( function () {
			$calc = $this->product->getPriceCalculator();
			$base = $calc->getBaseGrossPrice();
			$final = $calc->getYourGrossPrice();
			$formatted = $base > 0 || $base > $final ? wc_price( $base ) : '';
			return apply_filters( 'justb2b_display_base_gross_price', $formatted, $this );
		}, $this->cacheContext() );
	}

	public function getYourNetPrice(): string {
		return self::getFromRuntimeCache( function () {
			$price = $this->product->getPriceCalculator()->getYourNetPrice();
			$formatted = $price > 0 ? wc_price( $price ) : '';
			return apply_filters( 'justb2b_display_your_net_price', $formatted, $this );
		}, $this->cacheContext() );
	}

	public function getYourGrossPrice(): string {
		return self::getFromRuntimeCache( function () {
			$price = $this->product->getPriceCalculator()->getYourGrossPrice();
			$formatted = $price > 0 ? wc_price( $price ) : '';
			return apply_filters( 'justb2b_display_your_gross_price', $formatted, $this );
		}, $this->cacheContext() );
	}

	public function getYourNetTotal(): string {
		return self::getFromRuntimeCache( function () {
			if ( $this->product->getQty() <= 1 ) {
				return '';
			}
			$price = $this->product->getPriceCalculator()->getYourNetTotal();
			$formatted = $price > 0
				? wc_price( $price ) . ' (' . $this->product->getQty() . ')'
				: '';
			return apply_filters( 'justb2b_display_your_net_total', $formatted, $this );
		}, $this->cacheContext() );
	}

	public function getYourGrossTotal(): string {
		return self::getFromRuntimeCache( function () {
			if ( $this->product->getQty() <= 1 ) {
				return '';
			}

			$price = $this->product->getPriceCalculator()->getYourGrossTotal();
			$formatted = $price > 0
				? wc_price( $price ) . ' (' . $this->product->getQty() . ')'
				: '';

			return apply_filters( 'justb2b_display_your_gross_total', $formatted, $this );
		}, $this->cacheContext() );
	}

	public function getFinalNetPerItemPrice(): string {
		return self::getFromRuntimeCache( function () {
			$calc = $this->product->getPriceCalculator();
			$price = $calc->getFinalNetPerItemPrice();

			if ( $price < $calc->getYourNetPrice() ) {
				$formatted = $price > 0 ? wc_price( $price ) : '';
			} else {
				$formatted = '';
			}

			return apply_filters( 'justb2b_display_final_net_per_item_price', $formatted, $this );
		}, $this->cacheContext() );
	}

	public function getFinalGrossPerItemPrice(): string {
		return self::getFromRuntimeCache( function () {
			$calc = $this->product->getPriceCalculator();
			$price = $calc->getFinalGrossPerItemPrice();

			if ( $price < $calc->getYourGrossPrice() ) {
				$formatted = $price > 0 ? wc_price( $price ) : '';
			} else {
				$formatted = '';
			}

			return apply_filters( 'justb2b_display_final_gross_per_item_price', $formatted, $this );
		}, $this->cacheContext() );
	}

	public function getGiftsSaleNetTotal(): string {
		return self::getFromRuntimeCache( function () {
			$calc = $this->product->getPriceCalculator();
			$price = $calc->getGiftsSaleNetTotal();
			$numberOfGifts = $calc->getNumberfOfGifts();

			$formatted = $price > 0
				? wc_price( $price ) . ' (' . $numberOfGifts . ')'
				: '';

			return apply_filters( 'justb2b_display_gifts_sale_net_total', $formatted, $this );
		}, $this->cacheContext() );
	}


	public function getGiftsSaleGrossTotal(): string {
		return self::getFromRuntimeCache( function () {
			$calc = $this->product->getPriceCalculator();
			$price = $calc->getGiftsSaleGrossTotal();
			$numberOfGifts = $calc->getNumberfOfGifts();

			$formatted = $price > 0
				? wc_price( $price ) . ' (' . $numberOfGifts . ')'
				: '';

			return apply_filters( 'justb2b_display_gifts_sale_gross_total', $formatted, $this );
		}, $this->cacheContext() );
	}

	public function getFinalNetTotal(): string {
		return self::getFromRuntimeCache( function () {
			$calc = $this->product->getPriceCalculator();
			$price = $calc->getFinalNetTotal();

			if ( $calc->getYourNetTotal() > $price ) {
				$formatted = $price > 0
					? wc_price( $price ) . ' (' . $this->product->getQty() . ')'
					: '';
			} else {
				$formatted = '';
			}

			return apply_filters( 'justb2b_display_final_net_total', $formatted, $this );
		}, $this->cacheContext() );
	}

	public function getFinalGrossTotal(): string {
		return self::getFromRuntimeCache( function () {
			$calc = $this->product->getPriceCalculator();
			$price = $calc->getFinalGrossTotal();

			if ( $calc->getYourGrossTotal() > $price ) {
				$formatted = $price > 0
					? wc_price( $price ) . ' (' . $this->product->getQty() . ')'
					: '';
			} else {
				$formatted = '';
			}

			return apply_filters( 'justb2b_display_final_gross_total', $formatted, $this );
		}, $this->cacheContext() );
	}

	public function getRRPNet(): string {
		return self::getFromRuntimeCache( function () {
			$price = $this->product->getPriceCalculator()->getRRPNet();
			$formatted = $price > 0 ? wc_price( $price ) : '';

			return apply_filters( 'justb2b_display_rrp_net', $formatted, $this );
		}, $this->cacheContext() );
	}

	public function getRRPGross(): string {
		return self::getFromRuntimeCache( function () {
			$price = $this->product->getPriceCalculator()->getRRPGross();
			$formatted = $price > 0 ? wc_price( $price ) : '';

			return apply_filters( 'justb2b_display_rrp_gross', $formatted, $this );
		}, $this->cacheContext() );
	}


	/**
	 * @feature price_display conditional_visibility
	 * @title[ru] Условная видимость цен
	 * @desc[ru] Позволяет настраивать отображение отдельных блоков цен (брутто, нетто, RRP) в зависимости от роли пользователя и контекста (каталог, карточка).
	 * @order 510
	 */
	protected function showPriceByKey( string $key ): bool {
		$currentUser = UsersController::getCurrentUser();
		$userKind = $currentUser->isB2b() ? 'b2b' : 'b2c';
		$place = $this->isInLoop ? 'loop' : 'single';
		$finalKey = "{$place}_{$userKind}_{$key}_visibility";

		$globalController = GlobalController::getInstance();
		$settingsObject = $globalController->getSettingsModelObject();
		$value = $settingsObject->getFieldValue( $finalKey );
		$visible = $value !== 'hide';

		return apply_filters( "justb2b_display_show_price__{$key}", $visible, $this );
	}

	/**
	 * @feature price_display formatted_price_blocks
	 * @title[ru] Форматированные HTML-блоки цен
	 * @desc[ru] Каждая цена оборачивается в стилизованные блоки с префиксами и постфиксами, заданными в настройках.
	 * @order 520
	 */
	public function getPriceItem( string $key, string $price ): string {
		$html = '';
		if ( ! empty( $price ) && $this->showPriceByKey( $key ) ) {
			$prefix = $this->getPriceTail( $key, true );
			$postfix = $this->getPriceTail( $key, false );
			$class = 'justb2b-price justb2b-price-' . str_replace( '_', '-', $key );

			$html .= <<<HTML
            <div class="{$class}">
                {$prefix}
                <div class="justb2b-price-value">{$price}</div>
                {$postfix}
            </div>
            HTML;
		}
		return apply_filters( "justb2b_display_price_item_html__{$key}", $html, $key, $price, $this );
	}


	public function getPriceTail( $key, $isPrefix ): string {
		$currentUser = UsersController::getCurrentUser();
		$userKind = $currentUser->isB2b() ? 'b2b' : 'b2c';
		$place = $this->isInLoop ? 'loop' : 'single';
		$position = $isPrefix ? 'prefix' : 'postfix';
		$finalKey = "{$place}_{$userKind}_{$key}_{$position}";
		$globalController = GlobalController::getInstance();
		$settingsObject = $globalController->getSettingsModelObject();
		$value = $settingsObject->getFieldValue( $finalKey );
		$html = empty( $value )
			? ''
			: "<div class=\"justb2b-{$position}\">{$value}</div>";

		return apply_filters( "justb2b_display_price_tail__{$finalKey}", $html, $this );
	}

	public function getPricePriority( string $key ): int {
		$currentUser = UsersController::getCurrentUser();
		$userKind = $currentUser->isB2b() ? 'b2b' : 'b2c';
		$place = $this->isInLoop ? 'loop' : 'single';
		$finalKey = "{$place}_{$userKind}_{$key}_priority";

		$globalController = GlobalController::getInstance();
		$settingsObject = $globalController->getSettingsModelObject();
		$priority = (int) $settingsObject->getFieldValue( $finalKey );

		return apply_filters( "justb2b_display_price_priority__{$finalKey}", $priority, $this );
	}


	protected function renderPrioritizedPriceItems(): string {

		$priceItems = [ 
			'base_net' => [ $this, 'getBaseNetPrice' ],
			'base_gross' => [ $this, 'getBaseGrossPrice' ],
			'your_net' => [ $this, 'getYourNetPrice' ],
			'your_gross' => [ $this, 'getYourGrossPrice' ],
			'your_net_total' => [ $this, 'getYourNetTotal' ],
			'your_gross_total' => [ $this, 'getYourGrossTotal' ],
			'gifts_net_total' => [ $this, 'getGiftsSaleNetTotal' ],
			'gifts_gross_total' => [ $this, 'getGiftsSaleGrossTotal' ],
			'final_net_total' => [ $this, 'getFinalNetTotal' ],
			'final_gross_total' => [ $this, 'getFinalGrossTotal' ],
			'final_per_item_net' => [ $this, 'getFinalNetPerItemPrice' ],
			'final_per_item_gross' => [ $this, 'getFinalGrossPerItemPrice' ],
			'rrp_net' => [ $this, 'getRRPNet' ],
			'rrp_gross' => [ $this, 'getRRPGross' ],
		];

		$prioritizedItems = [];

		foreach ( $priceItems as $key => $callback ) {
			$priority = $this->getPricePriority( $key );
			$prioritizedItems[] = compact( 'key', 'callback', 'priority' );
		}

		usort( $prioritizedItems, fn( $a, $b ) => $a['priority'] <=> $b['priority'] );

		$html = '';

		foreach ( $prioritizedItems as $item ) {
			$price = call_user_func( $item['callback'] );
			$html .= $this->getPriceItem( $item['key'], $price );
		}

		return apply_filters( 'justb2b_display_render_prioritized_price_items', $html, $this );
	}

	public function renderPricesHtml() {
		if ( $this->product->isSimpleProduct() ) {
			$rule = $this->product->getFirstFullFitRule();
			if ( $rule ) {
				$html = '';
				$shouldRender = ( $this->isInLoop && ! $rule->isPricesInLoopHidden() )
					|| ( ! $this->isInLoop && ! $rule->isPricesInProductHidden() );

				if ( $shouldRender ) {
					$html .= $this->renderPrioritizedPriceItems();
				}

				if ( ! $this->isInLoop ) {
					$html .= $this->getCustomHtml1();
				}

				if ( current_user_can( 'administrator' ) ) {
					$html .= "<div class=\"justb2b-rule-title\">" . esc_html( $rule->getTitle() ) . "</div>";
				}

				
				return apply_filters(
					'justb2b_display_prices_html',
					$this->handlePricesHtmlContainer( $html ),
					$this,
					$rule
				);
			}
		}
		return apply_filters(
			'justb2b_display_prices_html',
			$this->handlePricesHtmlContainer( $this->defaultPriceHtml ),
			$this,
			null
		);
	}


	public function handlePricesHtmlContainer( string $pricesHtml ): string {
		// Bail early in loops or on simple-product AJAX calls.
		if (
			$this->isInLoop
			|| ! $this->product->isSimpleProduct()
			|| ( $this->product->isSimpleProduct() && defined( 'DOING_AJAX' ) )
		) {
			return apply_filters( 'justb2b_display_prices_html_container', $pricesHtml, $this );
		}

		$productId = $this->product->getID();
		$qtyTable = $this->getQtyTable();
		$b2cHtml = $this->getHtml();

		// Build the container + extra HTML.
		$output = sprintf(
			'<div class="justb2b_product" data-product_id="%1$d">%2$s</div>%3$s%4$s',
			esc_attr( $productId ),
			$pricesHtml,
			$qtyTable,
			$b2cHtml
		);

		return apply_filters( 'justb2b_display_prices_html_container', $output, $this );
	}


	/**
	 * @feature price_display quantity_table
	 * @title[ru] Таблица цен в зависимости от количества
	 * @desc[ru] Отображает на фронтенде таблицу с правилами ценообразования по количеству, включая источник цены, приоритет и границы.
	 * @order 530
	 */
	public function getQtyTable(): string {
		$key = 'qty_table';

		if ( ! $this->showPriceByKey( $key ) ) {
			return '';
		}

		$rules = $this->getVisibleRules();
		if ( empty( $rules ) ) {
			return '';
		}

		$prefix = $this->getPriceTail( $key, true );
		$postfix = $this->getPriceTail( $key, false );

		$html = $this->renderQtyTableHtml( $rules, $prefix, $postfix );

		return apply_filters( 'justb2b_display_qty_table_html', $html, $this );
	}

	private function getVisibleRules(): array {
		$rules = $this->product->getProductRules() ?? [];
		$result = array_filter( $rules, callback: fn( $rule ) => $rule->showInQtyTable() );
		return apply_filters( 'justb2b_display_visible_qty_table_rules', $result, $this );
	}

	private function renderQtyTableHtml( array $rules, string $prefix, string $postfix ): string {
		$rows = array_map( fn( $rule ) => $this->renderQtyTableRow( $rule ), $rules );

		$html = implode( '', [ 
			'<div class="justb2b-qty-table-container">',
			$prefix,
			'<div class="justb2b-qty-table">',
			'<table>',
			'<thead>',
			'<tr>',
			'<th>' . esc_html__( 'Title', 'justb2b' ) . '</th>',
			'<th>' . esc_html__( 'Price source', 'justb2b' ) . '</th>',
			'<th>' . esc_html__( 'Priority', 'justb2b' ) . '</th>',
			'<th>' . esc_html__( 'Min qty', 'justb2b' ) . '</th>',
			'<th>' . esc_html__( 'Max qty', 'justb2b' ) . '</th>',
			'<th>' . esc_html__( 'Price', 'justb2b' ) . '</th>',
			'</tr>',
			implode( '', $rows ),
			'</table>',
			'</div>',
			$postfix,
			'</div>',
		] );

		return apply_filters( 'justb2b_display_qty_table_html', $html, $this, $rules, $prefix, $postfix );
	}

	private function renderQtyTableRow( $rule ): string {
		$priceCalculator = $this->product->getPriceCalculator();
		$price = $priceCalculator->calcRule();

		$html = implode( '', [ 
			'<tr>',
			'<td>' . esc_html( $rule->getTitle() ) . '</td>',
			'<td>' . esc_html( $rule->getPrimaryPriceSource() ) . '</td>',
			'<td>' . esc_html( $rule->getPriority() ) . '</td>',
			'<td>' . esc_html( $rule->getMinQty() ) . '</td>',
			'<td>' . esc_html( $rule->getMaxQty() ) . '</td>',
			'<td>' . wc_price( $price ) . '</td>',
			'</tr>',
		] );

		return apply_filters( 'justb2b_display_qty_table_row_html', $html, $rule, $this );
	}

	/**
	 * @feature price_display dynamic_html
	 * @title[ru] Динамическое HTML-содержимое
	 * @desc[ru] Позволяет отображать настраиваемый HTML под ценами на основе настроек администратора и роли пользователя.
	 * @order 540
	 */
	public function getHtml(): string {
		if ( ! $this->isInLoop ) {
			$globalController = GlobalController::getInstance();
			$settingsObject = $globalController->getSettingsModelObject();
			$userType = UsersController::getCurrentUser()->isB2b() ? 'b2b' : 'b2c';
			$showHtml = $settingsObject->getFieldValue( "show_{$userType}_html_1" );
			if ( $showHtml ) {
				$html = $settingsObject->getFieldValue( "{$userType}_html_1" );
				$result = $this->getFormattedHtml( $html, "justb2b-{$userType}-html" );
				return apply_filters( 'justb2b_display_custom_html_1', $result, $this, $html );
			}
		}
		return apply_filters( 'justb2b_display_custom_html_1', '', $this, null );
	}

	private function getFormattedHtml( ?string $html, string $wrapperClass ): string {
		if ( ! empty( trim( $html ?? '' ) ) ) {
			$result = '<div class="' . esc_attr( $wrapperClass ) . '">' . apply_filters( 'the_content', $html ) . '</div>';
			return apply_filters( 'justb2b_display_custom_html_formatted', $result, $this, $html, $wrapperClass );
		}
		return apply_filters( 'justb2b_display_custom_html_formatted', '', $this, $html, $wrapperClass );
	}

	private function getCustomHtml1(): string {
		$rule = $this->product->getFirstFullFitRule();
		if ( $rule ) {
			$ruleHtml1 = $rule->getFieldValue( 'custom_html_1' );
			$result = $this->getFormattedHtml( $ruleHtml1, 'justb2b-rule-html-1' );
			return apply_filters( 'justb2b_display_rule_custom_html_1', $result, $this, $ruleHtml1 );
		}
		return apply_filters( 'justb2b_display_rule_custom_html_1', '', $this, null );
	}
}
