<?php

namespace JustB2b\Models;

use JustB2b\Fields\AssociationField;
use JustB2b\Fields\AssociationProductsField;
use JustB2b\Fields\AssociationRolesField;
use JustB2b\Fields\AssociationTermsField;
use JustB2b\Fields\AssociationUsersField;
use JustB2b\Fields\NumberField;
use JustB2b\Fields\RichText;
use JustB2b\Fields\SelectField;
use JustB2b\Fields\TextField;
use JustB2b\Controllers\UsersController;
use JustB2b\Traits\LazyLoaderTrait;

defined('ABSPATH') || exit;

class RuleModel extends AbrstractPostModel
{
    use LazyLoaderTrait;

    protected static string $key = 'rule';
    protected ProductModel $product;
    protected int $conditionQty;
    protected ?int $priority = null;
    protected ?string $kind = null;
    protected ?string $primaryPriceSource = null;
    protected ?string $secondaryPriceSource = null;
    protected ?string $secondaryRRPSource = null;
    protected ?float $value = null;
    protected ?int $minQty = null;
    protected ?int $maxQty = null;
    protected ?bool $showInQtyTable = null;
    protected ?bool $doesQtyFits = null;
    protected ?bool $isAssociationFit = null;
    protected ?bool $isPurchasable = null;
    protected ?bool $isInLoopHidden = null;
    protected ?bool $isFullyHidden = null;
    protected ?bool $isZeroRequestPrice = null;
    protected ?bool $isPricesInLoopHidden = null;
    protected ?bool $isPricesInProductHidden = null;

    public function __construct(
        int $id,
        ProductModel $product
    ) {
        parent::__construct($id);
        $this->initProduct($product);
    }

    public static function getSingleName(): string
    {
        return __('Rule', 'justb2b');
    }
    public static function getPluralName(): string
    {
        return __('Rules', 'justb2b');
    }

    public function getPriority(): int
    {
        $this->initPriority();
        return $this->priority;
    }


    protected function initPriority(): void
    {
        $this->lazyLoad($this->priority, function () {
            return $this->getFieldValue('priority');
        });
    }

    public function getKind(): string
    {
        $this->initKind();
        return $this->kind;
    }

    protected function initKind(): void
    {
        $this->lazyLoad($this->kind, function () {
            return $this->getFieldValue('kind');
        });
    }

    public function getPrimaryPriceSource(): string
    {
        $this->initPrimaryPriceSource();
        return $this->primaryPriceSource;
    }

    protected function initPrimaryPriceSource(): void
    {
        $this->lazyLoad($this->primaryPriceSource, function () {
            return $this->getFieldValue('primary_price_source');
        });
    }

    public function getSecondaryPriceSource(): string
    {
        $this->initSecondaryPriceSource();
        return $this->secondaryPriceSource;
    }

    protected function initSecondaryPriceSource(): void
    {
        $this->lazyLoad($this->secondaryPriceSource, function () {
            return $this->getFieldValue('secondary_price_source');
        });
    }

    public function getSecondaryRRPSource(): string
    {
        $this->initSecondaryRRPSource();
        return $this->secondaryRRPSource;
    }

    protected function initSecondaryRRPSource(): void
    {
        $this->lazyLoad($this->secondaryRRPSource, function () {
            return $this->getFieldValue('secondary_rrp_source');
        });
    }


    public function getValue(): float
    {
        $this->initValue();
        return $this->value;
    }

    protected function initValue(): void
    {
        $this->lazyLoad($this->value, function () {
            return $this->getFieldValue('value');
        });
    }

    public function getMinQty(): int
    {
        $this->initMinQty();
        return $this->minQty;
    }

    protected function initMinQty(): void
    {
        $this->lazyLoad($this->minQty, function () {
            return (int) $this->getFieldValue('min_qty');
        });
    }

    public function getMaxQty(): int
    {
        $this->initMaxQty();
        return $this->maxQty;
    }

    protected function initMaxQty(): void
    {
        $this->lazyLoad($this->maxQty, function () {
            return (int) $this->getFieldValue('max_qty');
        });
    }

    public function showInQtyTable(): bool
    {
        $this->initShowInQtyTable();
        return $this->showInQtyTable;
    }

    protected function initShowInQtyTable(): void
    {
        $this->lazyLoad($this->showInQtyTable, function () {
            $value = $this->getFieldValue('show_in_qty_table');
            return $value !== 'hide';
        });
    }

    public function doesQtyFits(): bool
    {
        $this->initDoesQtyFits();
        return $this->doesQtyFits;
    }

    protected function initDoesQtyFits(): void
    {
        $product = $this->getProduct();

        $this->lazyLoad($this->doesQtyFits, function () use ($product): bool {

            return ($this->getMinQty() === 0 || $this->getMinQty() <= $product->getQty())
                && ($this->getMaxQty() === 0 || $product->getQty() <= $this->getMaxQty());
        });
    }


    public function isAssociationFit(): bool
    {
        $this->initAssociationFit();
        return $this->isAssociationFit;
    }

    protected function initAssociationFit(): void
    {
        $this->lazyLoad($this->isAssociationFit, function () {
            $userController = UsersController::getInstance();
            $currentUser = $userController->getCurrentUser();
            $currentUserId = $currentUser->getId();
            $product = $this->getProduct();
            $productId = $product->getId();


            if (!$this->passesMainUsersRolesCheck($currentUserId)) {
                return false;
            }

            if (!$this->passesMainProductsTermsCheck($productId)) {
                return false;
            }


            if (!$this->passesQualifyingRolesCheck($currentUserId)) {
                return false;
            }


            if (!$this->passesQualifyingTermsCheck($productId)) {
                return false;
            }


            if (!$this->passesExcludingUsersRolesCheck($currentUserId)) {
                return false;
            }


            if (!$this->passesExcludingProductsTermsCheck($productId)) {
                return false;
            }


            return true;
        });
    }

    private function passesMainUsersRolesCheck(int $userId): bool
    {
        $users = $this->getFieldValue('users');
        $roles = $this->getFieldValue('roles');

        if (false === $users || false === $roles) {
            return false;
        }

        $hasUsers = !empty($users);
        $hasRoles = !empty($roles);

        if (!$hasUsers && !$hasRoles) {
            return true;
        }

        if ($hasUsers && $this->checkUsers($users, $userId)) {
            return true;
        }

        if ($hasRoles && $this->checkRoles($roles, $userId)) {
            return true;
        }

        return false;
    }

    private function passesMainProductsTermsCheck(int $productId): bool
    {
        $products = $this->getFieldValue('products');
        $terms = $this->getFieldValue('woo_terms');

        if (false === $products || false === $terms) {
            return false;
        }

        $hasProducts = !empty($products);
        $hasTerms = !empty($terms);

        if (!$hasProducts && !$hasTerms) {
            return true;
        }

        if ($hasProducts && $this->checkProduct($products, $productId)) {
            return true;
        }

        if ($hasTerms && $this->checkTerms($terms, $productId)) {
            return true;
        }

        return false;
    }

    private function passesQualifyingRolesCheck(int $userId): bool
    {
        $qualifyingRoles = $this->getFieldValue('qualifying_roles');

        if (false === $qualifyingRoles) {
            return false;
        }

        $hasQualifyingRoles = !empty($usequalifyingRolesrs);
        if (!$hasQualifyingRoles) {
            return true;
        }

        return $this->checkRoles($qualifyingRoles, $userId);
    }

    private function passesQualifyingTermsCheck(int $productId): bool
    {
        $qualifyingTerms = $this->getFieldValue('qualifying_woo_terms');

        if (false === $qualifyingTerms) {
            return false;
        }

        $hasQualifyingTerms = !empty($qualifyingTerms);
        if (!$hasQualifyingTerms) {
            return true;
        }

        return $this->checkTerms($qualifyingTerms, $productId);
    }

    private function passesExcludingUsersRolesCheck(int $userId): bool
    {
        $excludingUsers = $this->getFieldValue('excluding_users');

        if (false === $excludingUsers) {
            return false;
        }

        if ($this->checkUsers($excludingUsers, $userId)) {
            return false;
        }

        $excludingRoles = $this->getFieldValue('excluding_roles');

        if (false === $excludingRoles) {
            return false;
        }

        return !$this->checkRoles($excludingRoles, $userId);
    }

    private function passesExcludingProductsTermsCheck(int $productId): bool
    {
        $excludingProducts = $this->getFieldValue('excluding_products');

        if (false === $excludingProducts) {
            return false;
        }

        if ($this->checkProduct($excludingProducts, $productId)) {
            return false;
        }

        $excludingTerms = $this->getFieldValue('excluding_woo_terms');

        if (false === $excludingTerms) {
            return false;
        }

        return !$this->checkTerms($excludingTerms, $productId);
    }

    protected function checkProduct(false|array $products, int $productId): bool
    {
        $result = isset($products[$productId]);
        return $result;
    }

    protected function checkTerms(false|array $terms, int $productId): bool
    {
        $result = false;
        foreach ($terms as $term) {
            if (has_term($term['id'], $term['taxonomy'], $productId)) {
                $result = true;
                break;
            }
        }
        return $result;
    }

    protected function checkUsers(false|array $users, int $userId): bool
    {
        $result = false;
        if (isset($users[$userId])) {
            return true;
        }
        return $result;
    }

    protected function checkRoles(false|array $roles, int $userId): bool
    {
        $result = false;
        foreach ($roles as $role) {
            /** @var AssociationField $field */
            $field = $this->getField('users');
            $users = $field->getPostFieldValue($role['id']);
            if (isset($users[$userId])) {
                $result = true;
                break;
            }
        }
        return $result;
    }

    public function getProduct(): ProductModel
    {
        return $this->product;
    }

    protected function initProduct(ProductModel $product): void
    {
        $this->product = $product;
    }

    public function isPurchasable(): bool
    {
        $this->lazyLoad($this->isPurchasable, [$this, 'initIsPurchasable']);
        return $this->isPurchasable;
    }

    protected function initIsPurchasable(): bool
    {
        return $this->getKind() !== 'non_purchasable';
    }

    public function isInLoopHidden(): bool
    {
        $this->lazyLoad($this->isInLoopHidden, [$this, 'initisInLoopHidden']);
        return $this->isInLoopHidden;
    }

    protected function initisInLoopHidden(): bool
    {
        $value = $this->getFieldValue('visibility');
        return in_array($value, ['loop_hidden', 'fully_hidden'], true);
    }

    public function isFullyHidden(): bool
    {

        $this->lazyLoad($this->isFullyHidden, [$this, 'initIsFullyHidden']);
        return $this->isFullyHidden;
    }

    protected function initIsFullyHidden(): bool
    {
        $value = $this->getFieldValue('visibility');
        return $value === 'fully_hidden';
    }

    public function isZeroRequestPrice(): bool
    {
        $this->lazyLoad($this->isZeroRequestPrice, [$this, 'initIsZeroRequestPrice']);
        return $this->isZeroRequestPrice;
    }

    protected function initIsZeroRequestPrice(): bool
    {
        return $this->getKind() === 'zero_order_for_price';
    }

    public function isPricesInLoopHidden(): bool
    {
        $this->lazyLoad($this->isPricesInLoopHidden, [$this, 'initIsPricesInLoopHidden']);
        return $this->isPricesInLoopHidden;
    }

    protected function initIsPricesInLoopHidden(): bool
    {
        $value = $this->getFieldValue('all_prices_visibility');
        $allPricesVisibility = $value ?: 'show';
        return $allPricesVisibility === 'hide' || $allPricesVisibility === 'only_product';
    }

    public function isPricesInProductHidden(): bool
    {
        $this->lazyLoad($this->isPricesInProductHidden, [$this, 'initIsPricesInProductHidden']);
        return $this->isPricesInProductHidden;
    }

    protected function initIsPricesInProductHidden(): bool
    {
        $value = $this->getFieldValue('all_prices_visibility');
        $allPricesVisibility = $value ?: 'show';
        return $allPricesVisibility === 'hide' || $allPricesVisibility === 'only_loop';
    }

    protected static function getPrimaryPriceSources(): array
    {
        return [
            '_price' => '_price',
            '_regular_price' => '_regular_price',
            '_sale_price' => '_sale_price',
            'rrp_price' => 'rrp_price',
            'base_price_1' => 'base_price_1',
            'base_price_2' => 'base_price_2',
            'base_price_3' => 'base_price_3',
            'base_price_4' => 'base_price_4',
            'base_price_5' => 'base_price_5',
        ];
    }

    protected static function getSecondaryPriceSources(): array
    {
        return ['disabled' => 'disabled'] + self::getPrimaryPriceSources();
    }

    public static function getFieldsDefinition(): array
    {
        return [
            (new NumberField('priority', 'Priority'))
                ->setHelpText('Lower number = higher priority. Use gaps like 10, 20, 30. Defaults to 0.')
                ->setWidth(25),

            (new SelectField('user_type', 'User type'))
                ->setOptions(['b2x' => 'b2x', 'b2b' => 'b2b', 'b2c' => 'b2c'])
                ->setHelpText('Target user type. b2x means all users.')
                ->setWidth(25),

            (new SelectField('visibility', 'Visibility'))
                ->setOptions(['show' => 'show', 'fully_hidden' => 'fully_hidden'])
                ->setHelpText('Controls visibility. Fully hidden = not shown at all.')
                ->setWidth(25),

            (new SelectField('primary_price_source', 'Primary price source'))
                ->setOptions(self::getPrimaryPriceSources())
                ->setHelpText('Main price source used for calculation.')
                ->setWidth(25),

            (new SelectField('secondary_price_source', 'Secondary price source'))
                ->setOptions(self::getSecondaryPriceSources())
                ->setHelpText('Fallback if primary price is 0.')
                ->setWidth(25),

            (new SelectField('secondary_rrp_source', 'Secondary RPP source'))
                ->setOptions(self::getSecondaryPriceSources())
                ->setHelpText('Used if RRP is 0 or not set.')
                ->setWidth(25),

            (new SelectField('kind', 'Rodzaj'))
                ->setOptions([
                    'price_source' => 'price_source',
                    'net_minus_percent' => 'net_minus_percent',
                    'net_plus_percent' => 'net_plus_percent',
                    'net_minus_number' => 'net_minus_number',
                    'net_plus_number' => 'net_plus_number',
                    'net_equals_number' => 'net_equals_number',
                    'gross_minus_percent' => 'gross_minus_percent',
                    'gross_plus_percent' => 'gross_plus_percent',
                    'gross_minus_number' => 'gross_minus_number',
                    'gross_plus_number' => 'gross_plus_number',
                    'gross_equals_number' => 'gross_equals_number',
                    'non_purchasable' => 'non_purchasable',
                    'zero_order_for_price' => 'zero_order_for_price',
                ])
                ->setHelpText('How this rule changes the product price.')
                ->setWidth(25),

            (new TextField('value', 'Wartość'))
                ->setAttribute('type', 'number')
                ->setAttribute('step', 'any')
                ->setHelpText('Value used in price calculation.')
                ->setWidth(25),

            (new TextField('min_qty', 'Min ilość'))
                ->setAttribute('type', 'number')
                ->setAttribute('step', 'any')
                ->setHelpText('Min quantity to apply the rule. Defaults to 0.')
                ->setWidth(25),

            (new TextField('max_qty', 'Max ilość'))
                ->setAttribute('type', 'number')
                ->setAttribute('step', 'any')
                ->setHelpText('Max quantity to apply the rule. Empty = no limit.')
                ->setWidth(25),

            (new SelectField('all_prices_visibility', 'Prices visibility'))
                ->setOptions(['show' => 'show', 'hide' => 'hide', 'only_product' => 'only_product', 'only_loop' => 'only_loop'])
                ->setHelpText('Show/hide prices based on this rule.')
                ->setWidth(25),

            (new SelectField('show_in_qty_table', 'Pokazać w tabeli'))
                ->setOptions(['show' => 'show', 'hide' => 'hide'])
                ->setHelpText('Show this rule in the quantity table.')
                ->setWidth(25),

            (new RichText('custom_html_1', 'Custom HTML 1'))
                ->setHelpText('Optional HTML shown on the product page.')
                ->setWidth(100),

            (new AssociationUsersField('users', 'Users'))->setHelpText('Users the rule applies to. Empty = all (if no roles set).'),
            (new AssociationRolesField('roles', 'Roles'))->setHelpText('User roles the rule applies to. Empty = all (if no users set).'),
            (new AssociationProductsField('products', 'Products'))->setHelpText('Products the rule applies to. Empty = all (if no terms set).'),
            (new AssociationTermsField('woo_terms', 'Woo Terms'))->setHelpText('Product categories (terms) for this rule. Empty = all (if no products set).'),

            (new AssociationRolesField('qualifying_roles', 'Qualifying Roles'))->setHelpText('Filters products from the main conditions that qualify for the rule.'),
            (new AssociationTermsField('qualifying_woo_terms', 'Qualifying Woo Terms'))->setHelpText('Filters products from the main conditions that qualify for the rule.'),

            (new AssociationUsersField('excluding_users', 'Excluding Users'))->setHelpText('Users excluded from this rule.'),
            (new AssociationRolesField('excluding_roles', 'Excluding Roles'))->setHelpText('Roles excluded from this rule.'),
            (new AssociationProductsField('excluding_products', 'Excluding Products'))->setHelpText('Products excluded from this rule.'),
            (new AssociationTermsField('excluding_woo_terms', 'Excluding Woo Terms'))->setHelpText('Terms excluded from this rule.'),
        ];
    }
}