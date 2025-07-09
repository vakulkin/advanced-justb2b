<?php

namespace JustB2b\Models\Id;

use JustB2b\Controllers\Id\UsersController;
use JustB2b\Fields\AssociationProductsField;
use JustB2b\Fields\AssociationRolesField;
use JustB2b\Fields\AssociationTermsField;
use JustB2b\Fields\AssociationUsersField;
use JustB2b\Fields\ImageField;
use JustB2b\Fields\NonNegativeFloatField;
use JustB2b\Fields\NonNegativeIntegerField;
use JustB2b\Fields\RichTextField;
use JustB2b\Fields\SelectField;
use JustB2b\Integrations\WCMLIntegration;
use JustB2b\Traits\RuntimeCacheTrait;

defined( 'ABSPATH' ) || exit;

/**
 * @feature-section rules
 * @title[ru] Правила ценообразования
 * @title[pl] Reguły ustalania cen
 * @desc[ru] Управляют скидками, надбавками, видимостью цен и условиями покупки в зависимости от ролей, продуктов, категорий и количества.
 * @desc[pl] Zarządzają rabatami, narzutami, widocznością cen i warunkami zakupu w zależności od ról, produktów, kategorii i ilości.
 * @order 100
 */

/**
 * @feature rules types
 * @title[ru] Типы правил
 * @title[pl] Typy reguł
 * @desc[ru] Поддержка различных типов расчёта: процент, фиксированная сумма, итоговая цена, цена по источнику, RRP, запрет покупки и другие.
 * @desc[pl] Obsługa różnych typów obliczeń: procent, kwota stała, cena końcowa, cena ze źródła, RRP, zakaz zakupu i inne.
 * @order 101
 */

/**
 * @feature rules conditions
 * @title[ru] Условия применения
 * @title[pl] Warunki zastosowania
 * @desc[ru] Применение правил по ролям, пользователям, товарам, категориям и количеству. Поддерживаются исключения.
 * @desc[pl] Stosowanie reguł na podstawie ról, użytkowników, produktów, kategorii i ilości. Obsługiwane są wyjątki.
 * @order 102
 */

/**
 * @feature rules qty_limits
 * @title[ru] Ограничения по количеству
 * @title[pl] Ograniczenia ilościowe
 * @desc[ru] Настройка min/max количества, подарков и схем вроде «1 бесплатно за каждые 3».
 * @desc[pl] Ustawianie min./maks. ilości, gratisów i schematów typu „1 gratis na każde 3”.
 * @order 103
 */

/**
 * @feature rules price_sources
 * @title[ru] Источники цен
 * @title[pl] Źródła cen
 * @desc[ru] Указание основного и резервных источников цены и RRP для расчёта.
 * @desc[pl] Wskazanie głównego i zapasowych źródeł ceny oraz ceny katalogowej (RRP) do obliczeń.
 * @order 104
 */

/**
 * @feature rules visibility
 * @title[ru] Видимость правил и цен
 * @title[pl] Widoczność reguł i cen
 * @desc[ru] Управление отображением правил и цен — можно скрыть на странице товара, в списке или полностью.
 * @desc[pl] Zarządzanie widocznością reguł i cen – można je ukryć na stronie produktu, w liście lub całkowicie.
 * @order 105
 */

/**
 * @feature rules qty_table
 * @title[ru] Таблица цен по количеству
 * @title[pl] Tabela cen ilościowych
 * @desc[ru] Показывает правила с приоритетами и диапазонами количества. Можно включить или скрыть для каждого правила.
 * @desc[pl] Pokazuje reguły z priorytetami i zakresami ilości. Można je włączyć lub ukryć dla każdej reguły.
 * @order 106
 */

/**
 * @feature rules html_banner
 * @title[ru] Баннер и HTML под ценой
 * @title[pl] Baner i HTML pod ceną
 * @desc[ru] К каждому правилу можно привязать баннер или HTML-блок, отображаемый на странице товара.
 * @desc[pl] Do każdej reguły można dodać baner lub blok HTML wyświetlany na stronie produktu.
 * @order 107
 */

/**
 * @feature rules priority
 * @title[ru] Приоритет правил
 * @title[pl] Priorytet reguł
 * @desc[ru] У каждого правила есть числовой приоритет. Чем ниже значение, тем раньше применяется правило.
 * @desc[pl] Każda reguła ma wartość priorytetu. Im niższa liczba, tym wcześniej reguła zostanie zastosowana.
 * @order 108
 */

/**
 * @feature rules customer_type
 * @title[ru] Тип клиента
 * @title[pl] Typ klienta
 * @desc[ru] Правило может применяться только к B2B, только к B2C или ко всем клиентам.
 * @desc[pl] Reguła może być stosowana tylko dla klientów B2B, tylko dla B2C lub dla wszystkich.
 * @order 109
 */

class RuleModel extends AbstractPostModel {
	use RuntimeCacheTrait;
	protected int $productId;
	protected int $originLangProductId;
	protected int $qty;

	public function __construct(
		int $id,
		int $productId = 0,
		int $originLangProductId = 0,
		int $qty = 0,
	) {
		parent::__construct( $id );
		$this->productId = $productId;
		$this->originLangProductId = $originLangProductId;
		$this->qty = $qty;
	}

	protected function cacheContext( array $extra = [] ): array {
		return array_merge( [ 
			parent::cacheContext( $extra ),
			'product_id' => $this->productId,
			'qty' => $this->qty
		] );
	}

	public function getPriority(): int {
		return $this->getFieldValue( 'rule_priority' );
	}

	public function getKind(): string {
		return $this->getFieldValue( 'rule_kind' );
	}

	public function getPrimaryPriceSource(): string {
		return $this->getFieldValue( 'rule_primary_price_source' );
	}

	public function getSecondaryPriceSource(): string {
		return $this->getFieldValue( 'rule_secondary_price_source' );
	}

	public function getThirdPriceSource(): string {
		return $this->getFieldValue( 'rule_third_price_source' );
	}

	public function getPrimaryRRPSource(): string {
		return $this->getFieldValue( 'rule_primary_rrp_source' );
	}

	public function getSecondaryRRPSource(): string {
		return $this->getFieldValue( 'rule_secondary_rrp_source' );
	}

	public function getThirdRRPSource(): string {
		return $this->getFieldValue( 'rule_third_rrp_source' );
	}

	public function getValue(): float {
		return $this->getFieldValue( 'rule_value' );
	}

	public function getCurrency(): string {
		/** @var SelectField $selectField */
		$selectField = WCMLIntegration::currencyWPMLSelectField();
		$value = $this->getFieldValue( 'rule_currency' );
		if ( $selectField && isset( $selectField->getOptions()[ $value ] ) ) {
			return $value;
		}
		return $selectField->getDefaultValue();
	}

	public function getMinQty(): int {
		return $this->getFieldValue( 'rule_min_qty' );
	}

	public function getMaxQty(): int {
		return $this->getFieldValue( 'rule_max_qty' );
	}

	public function isEmptyMaxQty() {
		return $this->isEmptyField( 'rule_max_qty' );
	}

	public function getNumberOfFree(): int {
		return $this->getFieldValue( 'rule_free_number' );
	}

	public function getFreeEveryItems(): int {
		return $this->getFieldValue( 'rule_free_every_items' );
	}

	public function showInQtyTable(): bool {
		return $this->getFieldValue( 'rule_show_in_qty_table' ) !== 'hide';
	}

	public function getBanner(): string {
		return $this->getFieldValue( 'rule_banner' );
	}

	public function doesQtyFits(): bool {
		return self::getFromRuntimeCache( function () {
			$qty = $this->qty;

			return ( $this->getMinQty() <= $qty ) && ( $this->isEmptyMaxQty() || $qty <= $this->getMaxQty() );
		}, $this->cacheContext() );
	}

	public function isFullRuleFit(): bool {
		return self::getFromRuntimeCache( function () {

			error_log( print_r( [ $this->passesMainUsersRolesCheck()
				, $this->passesMainProductsTermsCheck()
				, $this->passesQualifyingRolesCheck()
				, $this->passesQualifyingTermsCheck()
				, $this->passesExcludingUsersRolesCheck()
				, $this->passesExcludingProductsTermsCheck() ], true ) );

			return $this->passesMainUsersRolesCheck()
				&& $this->passesMainProductsTermsCheck()
				&& $this->passesQualifyingRolesCheck()
				&& $this->passesQualifyingTermsCheck()
				&& $this->passesExcludingUsersRolesCheck()
				&& $this->passesExcludingProductsTermsCheck();
		}, $this->cacheContext() );
	}

	public function isRuleFitToUser() {
		return self::getFromRuntimeCache( function () {
			return $this->passesMainUsersRolesCheck()
				&& $this->passesQualifyingRolesCheck()
				&& $this->passesExcludingUsersRolesCheck();
		}, $this->cacheContext() );
	}

	public function isPurchasable(): bool {
		return self::getFromRuntimeCache(
			fn() => $this->getKind() !== 'non_purchasable',
			$this->cacheContext()
		);
	}


	public function isInLoopHidden(): bool {
		return self::getFromRuntimeCache( function () {
			$visibility = $this->getFieldValue( 'rule_visibility' );
			return in_array( $visibility, [ 'loop_hidden', 'fully_hidden' ], true );
		}, $this->cacheContext() );
	}

	public function isFullyHidden(): bool {
		return self::getFromRuntimeCache(
			fn() => $this->getFieldValue( 'rule_visibility' ) === 'fully_hidden',
			$this->cacheContext()
		);
	}

	public function isZeroRequestPrice(): bool {
		return self::getFromRuntimeCache(
			fn() => $this->getKind() === 'zero_order_for_price',
			$this->cacheContext()
		);
	}

	public function isPricesInLoopHidden(): bool {
		return self::getFromRuntimeCache( function () {
			$v = $this->getFieldValue( 'rule_all_prices_visibility' ) ?: 'show';
			return in_array( $v, [ 'hide', 'only_product' ], true );
		}, $this->cacheContext() );
	}

	public function isPricesInProductHidden(): bool {
		return self::getFromRuntimeCache( function () {
			$v = $this->getFieldValue( 'rule_all_prices_visibility' ) ?: 'show';
			return in_array( $v, [ 'hide', 'only_loop' ], true );
		}, $this->cacheContext() );
	}


	private function passesMainUsersRolesCheck(): bool {

		$users = $this->getFieldValue( 'rule_users' );
		if ( ! $this->allItemsValid( $users ) ) {
			return false;
		}

		$roles = $this->getFieldValue( 'rule_roles' );
		if ( ! $this->allItemsValid( $roles ) ) {
			return false;
		}

		$hasUsers = ! empty( $users );
		$hasRoles = ! empty( $roles );

		if ( ! $hasUsers && ! $hasRoles ) {
			return true;
		}

		if ( $hasUsers && $this->checkUsers( $users ) ) {
			return true;
		}

		if ( $hasRoles && $this->checkRoles( $roles ) ) {
			return true;
		}

		return false;
	}
	private function passesMainProductsTermsCheck(): bool {

		$products = $this->getFieldValue( 'rule_products' );
		if ( ! $this->allItemsValid( $products ) ) {
			return false;
		}

		$terms = $this->getFieldValue( 'rule_woo_terms' );
		if ( ! $this->allItemsValid( $terms ) ) {
			return false;
		}

		$hasProducts = ! empty( $products );
		$hasTerms = ! empty( $terms );

		if ( ! $hasProducts && ! $hasTerms ) {
			return true;
		}

		if ( $hasProducts && $this->checkProduct( $products ) ) {
			return true;
		}

		if ( $hasTerms && $this->checkTerms( $terms ) ) {
			return true;
		}
		return false;
	}

	private function passesQualifyingRolesCheck(): bool {
		$qualifyingRoles = $this->getFieldValue( 'rule_qualifying_roles' );

		if ( ! $this->allItemsValid( $qualifyingRoles ) ) {
			return false;
		}

		$hasQualifyingRoles = ! empty( $qualifyingRoles );
		if ( ! $hasQualifyingRoles ) {
			return true;
		}
		return $this->checkRoles( $qualifyingRoles );
	}

	private function passesQualifyingTermsCheck(): bool {
		$qualifyingTerms = $this->getFieldValue( 'rule_qualifying_woo_terms' );

		if ( ! $this->allItemsValid( $qualifyingTerms ) ) {
			return false;
		}

		$hasQualifyingTerms = ! empty( $qualifyingTerms );
		if ( ! $hasQualifyingTerms ) {
			return true;
		}

		return $this->checkTerms( $qualifyingTerms );
	}

	private function passesExcludingUsersRolesCheck(): bool {
		$excludingUsers = $this->getFieldValue( 'rule_excluding_users' );

		if ( ! $this->allItemsValid( $excludingUsers ) ) {
			return false;
		}

		$excludingRoles = $this->getFieldValue( 'rule_excluding_roles' );

		if ( ! $this->allItemsValid( $excludingRoles ) ) {
			return false;
		}

		if ( $this->checkUsers( $excludingUsers ) ) {
			return false;
		}

		return ! $this->checkRoles( $excludingRoles );
	}

	private function passesExcludingProductsTermsCheck(): bool {
		$excludingProducts = $this->getFieldValue( 'rule_excluding_products' );
		error_log( print_r( $excludingProducts, true ) );

		if ( ! $this->allItemsValid( $excludingProducts ) ) {
			return false;
		}

		$excludingTerms = $this->getFieldValue( 'rule_excluding_woo_terms' );

		if ( ! $this->allItemsValid( $excludingTerms ) ) {
			return false;
		}

		if ( $this->checkProduct( $excludingProducts ) ) {
			return false;
		}

		return ! $this->checkTerms( $excludingTerms );
	}

	protected function checkProduct( array $products ): bool {
		if ( isset( $products[ $this->productId ] ) ) {
			return true;
		}
		return apply_filters( "justb2b_check_product", false, $products, $this->productId );
	}


	protected function allItemsValid( array $items ): bool {
		foreach ( $items as $item ) {
			if ( $item['valid'] === false ) {
				return false;
			}
		}
		return true;
	}


	protected function checkTerms( array $terms ): bool {
		// Step 1: Exact match
		foreach ( $terms as $termId => $term ) {
			if ( has_term( $termId, $term['key'], $this->productId ) ) {
				return true;
			}
		}

		return apply_filters( 'checkTerms', false, $this->productId, $terms );
	}


	protected function checkUsers( array $users ): bool {
		$currentUser = UsersController::getCurrentUser();
		$currentUserId = $currentUser->getId();
		$result = false;
		if ( isset( $users[ $currentUserId ] ) ) {
			return true;
		}
		return $result;
	}

	protected function checkRoles( array $roles ): bool {
		$result = false;
		$currentUser = UsersController::getCurrentUser();
		$currentUserId = $currentUser->getId();
		foreach ( $roles as $role ) {
			/** @var AssociationUsersField $field */
			$role = new RoleModel( (int) $role );
			$users = $role->getFieldValue( 'role_users' );
			if ( isset( $users[ $currentUserId ] ) ) {
				$result = true;
				break;
			}
		}
		return $result;
	}

	protected static function getPrimaryPriceSources(): array {
		return [ 
			'_price' => 'Default Price (_price)',
			'_regular_price' => 'Regular Price (_regular_price)',
			'_sale_price' => 'Sale Price (_sale_price)',
			'base_price_1' => 'Base Price 1',
			'base_price_2' => 'Base Price 2',
			'base_price_3' => 'Base Price 3',
			'base_price_4' => 'Base Price 4',
			'base_price_5' => 'Base Price 5',
			'rrp_price' => 'RRP (Recommended Retail Price)',
			'currency_base_price_1' => 'Currency Base Price 1',
			'currency_base_price_2' => 'Currency Base Price 2',
			'currency_base_price_3' => 'Currency Base Price 3',
			'currency_base_price_4' => 'Currency Base Price 4',
			'currency_base_price_5' => 'Currency Base Price 5',
			'currency_rrp_price' => 'Currency RRP',
		];
	}


	protected static function getSecondaryPriceSources(): array {
		return [ 'disabled' => 'Disabled' ] + self::getPrimaryPriceSources();
	}

	public static function getFieldsDefinition(): array {
		return [ 
			( new NonNegativeIntegerField( 'rule_priority', 'Priority' ) )
				->setHelpText( 'Lower number = higher priority. Use gaps like 10, 20, 30. Defaults to 0.' )
				->setWidth( 25 ),
			( new SelectField( 'rule_customer_type', 'Customer type' ) )
				->setOptions( [ 
					'b2b' => 'Business customers (B2B)',
					'b2c' => 'Individual customers (B2C)',
					'b2x' => 'All customers (B2X)',
				] )
				->setHelpText( 'Target user type. b2x means all users.' )
				->setWidth( 25 ),

			( new SelectField( 'rule_visibility', 'Visibility' ) )
				->setOptions( [ 
					'show' => 'Show',
					'fully_hidden' => 'Fully hidden',
				] )
				->setHelpText( 'Controls visibility. Fully hidden = not shown at all.' )
				->setWidth( 25 ),

			( new SelectField( 'rule_primary_price_source', 'Primary price source' ) )
				->setOptions( self::getPrimaryPriceSources() )
				->setHelpText( 'Main price source used for calculation.' )
				->setWidth( 25 ),

			( new SelectField( 'rule_secondary_price_source', 'Secondary price source' ) )
				->setOptions( self::getSecondaryPriceSources() )
				->setHelpText( 'Fallback if final price is 0.' )
				->setWidth( 25 ),

			( new SelectField( 'rule_third_price_source', 'Third price source' ) )
				->setOptions( self::getSecondaryPriceSources() )
				->setHelpText( 'Fallback if final price is 0.' )
				->setWidth( 25 ),

			( new SelectField( 'rule_primary_rrp_source', 'RRP source' ) )
				->setOptions( self::getPrimaryPriceSources() )
				->setHelpText( 'Main RRP source.' )
				->setWidth( 25 ),

			( new SelectField( 'rule_secondary_rrp_source', 'Secondary RRP source' ) )
				->setOptions( self::getSecondaryPriceSources() )
				->setHelpText( 'Used if RRP is 0 or not set.' )
				->setWidth( 25 ),

			( new SelectField( 'rule_third_rrp_source', 'Third RRP source' ) )
				->setOptions( self::getSecondaryPriceSources() )
				->setHelpText( 'Used if RRP is 0 or not set.' )
				->setWidth( 25 ),

			( new SelectField( 'rule_kind', 'Rodzaj' ) )
				->setOptions( [ 
					'price_source' => 'Price source',
					'net_minus_percent' => 'Net - Percent',
					'net_plus_percent' => 'Net + Percent',
					'net_minus_number' => 'Net - Fixed amount',
					'net_plus_number' => 'Net + Fixed amount',
					'net_equals_number' => 'Net = Fixed amount',
					'gross_minus_percent' => 'Gross - Percent',
					'gross_plus_percent' => 'Gross + Percent',
					'gross_minus_number' => 'Gross - Fixed amount',
					'gross_plus_number' => 'Gross + Fixed amount',
					'gross_equals_number' => 'Gross = Fixed amount',
					'non_purchasable' => 'Non-purchasable',
					'zero_order_for_price' => '0 price & allow order',
				] )
				->setHelpText( 'How this rule changes the product price.' )
				->setWidth( 25 ),

			( new NonNegativeFloatField( 'rule_value', 'Wartość' ) )
				->setHelpText( 'Value used in price calculation.' )
				->setWidth( 25 ),

			WCMLIntegration::currencyWPMLSelectField(),

			( new NonNegativeIntegerField( 'rule_free_number', 'Number for free' ) )
				->setHelpText( 'Number of same product for free. Defaults to 0. Zero means no gifts.' )
				->setWidth( 25 ),

			( new NonNegativeIntegerField( 'rule_free_every_items', 'Add X for free every Y items' ) )
				->setHelpText( 'Add X free for every Y items. 0 means only once. Defaults to 0.' )
				->setWidth( 25 ),

			( new NonNegativeIntegerField( 'rule_min_qty', 'Min ilość' ) )
				->setHelpText( 'Min quantity to apply the rule. Defaults to 0.' )
				->setWidth( 25 ),

			( new NonNegativeIntegerField( 'rule_max_qty', 'Max ilość' ) )
				->setHelpText( 'Max quantity to apply the rule. Empty = no limit.' )
				->setWidth( 25 ),

			( new SelectField( 'rule_all_prices_visibility', 'Prices visibility' ) )
				->setOptions( [ 
					'show' => 'Show on product and loop',
					'hide' => 'Hide everywhere',
					'only_product' => 'Show only on product page',
					'only_loop' => 'Show only in product list',
				] )
				->setHelpText( 'Show/hide prices based on this rule.' )
				->setWidth( 25 ),

			( new SelectField( 'rule_show_in_qty_table', 'Pokazać w tabeli' ) )
				->setOptions( [ 
					'show' => 'Show',
					'hide' => 'Hide',
				] )
				->setHelpText( 'Show this rule in the quantity table.' )
				->setWidth( 25 ),

			( new RichTextField( 'rule_custom_html_1', 'Custom HTML 1' ) )
				->setHelpText( 'Optional HTML shown on the product page.' )
				->setWidth( 100 ),

			( new AssociationUsersField( 'rule_users', 'Users' ) )->setHelpText( 'Users the rule applies to. Empty = all (if no roles set).' ),
			( new AssociationRolesField( 'rule_roles', 'Roles' ) )->setHelpText( 'User roles the rule applies to. Empty = all (if no users set).' ),
			( new AssociationProductsField( 'rule_products', 'Products' ) )->setHelpText( 'Products the rule applies to. Empty = all (if no terms set).' ),
			( new AssociationTermsField( 'rule_woo_terms', 'Woo Terms' ) )->setHelpText( 'Product categories (terms) for this rule. Empty = all (if no products set).' ),

			( new AssociationRolesField( 'rule_qualifying_roles', 'Qualifying Roles' ) )->setHelpText( 'Filters products from the main conditions that qualify for the rule.' ),
			( new AssociationTermsField( 'rule_qualifying_woo_terms', 'Qualifying Woo Terms' ) )->setHelpText( 'Filters products from the main conditions that qualify for the rule.' ),

			( new AssociationUsersField( 'rule_excluding_users', 'Excluding Users' ) )->setHelpText( 'Users excluded from this rule.' ),
			( new AssociationRolesField( 'rule_excluding_roles', 'Excluding Roles' ) )->setHelpText( 'Roles excluded from this rule.' ),
			( new AssociationProductsField( 'rule_excluding_products', 'Excluding Products' ) )->setHelpText( 'Products excluded from this rule.' ),
			( new AssociationTermsField( 'rule_excluding_woo_terms', 'Excluding Woo Terms' ) )->setHelpText( 'Terms excluded from this rule.' ),

			( new ImageField( 'rule_banner', 'Banner' ) )->setHelpText( 'Banner for this rule.' ),
		];
	}
}
