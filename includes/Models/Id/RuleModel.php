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
use JustB2b\Fields\NumberField;
use JustB2b\Fields\RichTextField;
use JustB2b\Fields\SelectField;
use JustB2b\Integrations\WCMLIntegration;
use JustB2b\Traits\RuntimeCacheTrait;

defined( 'ABSPATH' ) || exit;

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
		return $this->getFieldValue( 'priority' );
	}

	public function getKind(): string {
		return $this->getFieldValue( 'kind' );
	}

	public function getPrimaryPriceSource(): string {
		return $this->getFieldValue( 'primary_price_source' );
	}

	public function getSecondaryPriceSource(): string {
		return $this->getFieldValue( 'secondary_price_source' );
	}

	public function getThirdPriceSource(): string {
		return $this->getFieldValue( 'third_price_source' );
	}

	public function getPrimaryRRPSource(): string {
		return $this->getFieldValue( 'primary_rrp_source' );
	}

	public function getSecondaryRRPSource(): string {
		return $this->getFieldValue( 'secondary_rrp_source' );
	}

	public function getThirdRRPSource(): string {
		return $this->getFieldValue( 'third_rrp_source' );
	}

	public function getValue(): float {
		return $this->getFieldValue( 'value' );
	}

	public function getCurrency(): string {
		/** @var SelectField $selectField */
		$selectField = WCMLIntegration::currencyWPMLSelectField();
		$value = $this->getFieldValue( 'currency' );
		if ( $selectField && isset( $selectField->getOptions()[ $value ] ) ) {
			return $value;
		}
		return $selectField->getDefaultValue();
	}

	public function getMinQty(): int {
		return $this->getFieldValue( 'min_qty' );
	}

	public function getMaxQty(): int {
		return $this->getFieldValue( 'max_qty' );
	}

	public function isEmptyMaxQty() {
		return $this->isEmptyField( 'max_qty' );
	}

	public function getNumberOfGifts(): int {
		return $this->getFieldValue( 'gifts_number' );
	}

	public function getGiftsEveryItems(): int {
		return $this->getFieldValue( 'gifts_every_items' );
	}

	public function showInQtyTable(): bool {
		return $this->getFieldValue( 'show_in_qty_table' ) !== 'hide';
	}

	public function getBanner(): string {
		return $this->getFieldValue( 'banner' );
	}

	public function doesQtyFits(): bool {
		return self::getFromRuntimeCache( function () {
			$qty = $this->qty;

			return ( $this->getMinQty() <= $qty ) && ( $this->isEmptyMaxQty() || $qty <= $this->getMaxQty() );
		}, $this->cacheContext() );
	}

	public function isFullRuleFit(): bool {
		return self::getFromRuntimeCache( function () {
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
			$visibility = $this->getFieldValue( 'visibility' );
			return in_array( $visibility, [ 'loop_hidden', 'fully_hidden' ], true );
		}, $this->cacheContext() );
	}

	public function isFullyHidden(): bool {
		return self::getFromRuntimeCache(
			fn() => $this->getFieldValue( 'visibility' ) === 'fully_hidden',
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
			$v = $this->getFieldValue( 'all_prices_visibility' ) ?: 'show';
			return in_array( $v, [ 'hide', 'only_product' ], true );
		}, $this->cacheContext() );
	}

	public function isPricesInProductHidden(): bool {
		return self::getFromRuntimeCache( function () {
			$v = $this->getFieldValue( 'all_prices_visibility' ) ?: 'show';
			return in_array( $v, [ 'hide', 'only_loop' ], true );
		}, $this->cacheContext() );
	}


	private function passesMainUsersRolesCheck(): bool {

		$users = $this->getFieldValue( 'users' );
		if ( ! $this->allItemsValid( $users ) ) {
			return false;
		}

		$roles = $this->getFieldValue( 'roles' );
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

		$products = $this->getFieldValue( 'products' );
		if ( ! $this->allItemsValid( $products ) ) {
			return false;
		}

		$terms = $this->getFieldValue( 'woo_terms' );
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
		$qualifyingRoles = $this->getFieldValue( 'qualifying_roles' );

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
		$qualifyingTerms = $this->getFieldValue( 'qualifying_woo_terms' );

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
		$excludingUsers = $this->getFieldValue( 'excluding_users' );

		if ( ! $this->allItemsValid( $excludingUsers ) ) {
			return false;
		}

		$excludingRoles = $this->getFieldValue( 'excluding_roles' );

		if ( ! $this->allItemsValid( $excludingRoles ) ) {
			return false;
		}

		if ( $this->checkUsers( $excludingUsers ) ) {
			return false;
		}

		return ! $this->checkRoles( $excludingRoles );
	}

	private function passesExcludingProductsTermsCheck(): bool {
		$excludingProducts = $this->getFieldValue( 'excluding_products' );
		if ( ! $this->allItemsValid( $excludingProducts ) ) {
			return false;
		}

		$excludingTerms = $this->getFieldValue( 'excluding_woo_terms' );

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
			$field = $this->getField( 'users' );
			$users = $field->getValue( $role );
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
			'rrp_price' => 'RRP (Recommended Retail Price)',
			'base_price_1' => 'Base Price 1',
			'base_price_2' => 'Base Price 2',
			'base_price_3' => 'Base Price 3',
			'base_price_4' => 'Base Price 4',
			'base_price_5' => 'Base Price 5',
			'currency_rrp_price' => 'Currency RRP',
			'currency_base_price_1' => 'Currency Base Price 1',
			'currency_base_price_2' => 'Currency Base Price 2',
			'currency_base_price_3' => 'Currency Base Price 3',
			'currency_base_price_4' => 'Currency Base Price 4',
			'currency_base_price_5' => 'Currency Base Price 5',
		];
	}


	protected static function getSecondaryPriceSources(): array {
		return [ 'disabled' => 'Disabled' ] + self::getPrimaryPriceSources();
	}

	public static function getFieldsDefinition(): array {
		return [ 
			( new NumberField( 'priority', 'Priority' ) )
				->setHelpText( 'Lower number = higher priority. Use gaps like 10, 20, 30. Defaults to 0.' )
				->setWidth( 25 ),
			( new SelectField( 'customer_type', 'Customer type' ) )
				->setOptions( [ 
					'b2b' => 'Business customers (B2B)',
					'b2c' => 'Individual customers (B2C)',
					'b2x' => 'All customers (B2X)',
				] )
				->setHelpText( 'Target user type. b2x means all users.' )
				->setWidth( 25 ),

			( new SelectField( 'visibility', 'Visibility' ) )
				->setOptions( [ 
					'show' => 'Show',
					'fully_hidden' => 'Fully hidden',
				] )
				->setHelpText( 'Controls visibility. Fully hidden = not shown at all.' )
				->setWidth( 25 ),

			( new SelectField( 'primary_price_source', 'Primary price source' ) )
				->setOptions( self::getPrimaryPriceSources() )
				->setHelpText( 'Main price source used for calculation.' )
				->setWidth( 25 ),

			( new SelectField( 'secondary_price_source', 'Secondary price source' ) )
				->setOptions( self::getSecondaryPriceSources() )
				->setHelpText( 'Fallback if final price is 0.' )
				->setWidth( 25 ),

			( new SelectField( 'third_price_source', 'Third price source' ) )
				->setOptions( self::getSecondaryPriceSources() )
				->setHelpText( 'Fallback if final price is 0.' )
				->setWidth( 25 ),

			( new SelectField( 'primary_rrp_source', 'RRP source' ) )
				->setOptions( self::getPrimaryPriceSources() )
				->setHelpText( 'Main RRP source.' )
				->setWidth( 25 ),

			( new SelectField( 'secondary_rrp_source', 'Secondary RRP source' ) )
				->setOptions( self::getSecondaryPriceSources() )
				->setHelpText( 'Used if RRP is 0 or not set.' )
				->setWidth( 25 ),

			( new SelectField( 'third_rrp_source', 'Third RRP source' ) )
				->setOptions( self::getSecondaryPriceSources() )
				->setHelpText( 'Used if RRP is 0 or not set.' )
				->setWidth( 25 ),

			( new SelectField( 'kind', 'Rodzaj' ) )
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

			( new NonNegativeFloatField( 'value', 'Wartość' ) )
				->setHelpText( 'Value used in price calculation.' )
				->setWidth( 25 ),

			// $WCMLIntegration::currencyWPMLSelectField(),

			( new NonNegativeIntegerField( 'gifts_number', 'Number of gifts' ) )
				->setHelpText( 'Number of same product gifts for this product. Defaults to 0. Zero means no gifts.' )
				->setWidth( 25 ),

			( new NonNegativeIntegerField( 'gifts_every_items', 'Add gitfs every Y items' ) )
				->setHelpText( 'Add X new gifts for every Y items. 0 means only once. Defaults to 0.' )
				->setWidth( 25 ),

			( new NonNegativeIntegerField( 'min_qty', 'Min ilość' ) )
				->setHelpText( 'Min quantity to apply the rule. Defaults to 0.' )
				->setWidth( 25 ),

			( new NonNegativeIntegerField( 'max_qty', 'Max ilość' ) )
				->setHelpText( 'Max quantity to apply the rule. Empty = no limit.' )
				->setWidth( 25 ),

			( new SelectField( 'all_prices_visibility', 'Prices visibility' ) )
				->setOptions( [ 
					'show' => 'Show on product and loop',
					'hide' => 'Hide everywhere',
					'only_product' => 'Show only on product page',
					'only_loop' => 'Show only in product list',
				] )
				->setHelpText( 'Show/hide prices based on this rule.' )
				->setWidth( 25 ),

			( new SelectField( 'show_in_qty_table', 'Pokazać w tabeli' ) )
				->setOptions( [ 
					'show' => 'Show',
					'hide' => 'Hide',
				] )
				->setHelpText( 'Show this rule in the quantity table.' )
				->setWidth( 25 ),

			( new RichTextField( 'custom_html_1', 'Custom HTML 1' ) )
				->setHelpText( 'Optional HTML shown on the product page.' )
				->setWidth( 100 ),

			( new AssociationUsersField( 'users', 'Users' ) )->setHelpText( 'Users the rule applies to. Empty = all (if no roles set).' ),
			( new AssociationRolesField( 'roles', 'Roles' ) )->setHelpText( 'User roles the rule applies to. Empty = all (if no users set).' ),
			( new AssociationProductsField( 'products', 'Products' ) )->setHelpText( 'Products the rule applies to. Empty = all (if no terms set).' ),
			( new AssociationTermsField( 'woo_terms', 'Woo Terms' ) )->setHelpText( 'Product categories (terms) for this rule. Empty = all (if no products set).' ),

			( new AssociationRolesField( 'qualifying_roles', 'Qualifying Roles' ) )->setHelpText( 'Filters products from the main conditions that qualify for the rule.' ),
			( new AssociationTermsField( 'qualifying_woo_terms', 'Qualifying Woo Terms' ) )->setHelpText( 'Filters products from the main conditions that qualify for the rule.' ),

			( new AssociationUsersField( 'excluding_users', 'Excluding Users' ) )->setHelpText( 'Users excluded from this rule.' ),
			( new AssociationRolesField( 'excluding_roles', 'Excluding Roles' ) )->setHelpText( 'Roles excluded from this rule.' ),
			( new AssociationProductsField( 'excluding_products', 'Excluding Products' ) )->setHelpText( 'Products excluded from this rule.' ),
			( new AssociationTermsField( 'excluding_woo_terms', 'Excluding Woo Terms' ) )->setHelpText( 'Terms excluded from this rule.' ),

			( new ImageField( 'banner', 'Banner' ) )->setHelpText( 'Banner for this rule.' ),
		];
	}
}
