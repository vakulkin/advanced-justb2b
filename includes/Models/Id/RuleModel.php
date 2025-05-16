<?php

namespace JustB2b\Models\Id;

use JustB2b\Controllers\Id\UsersController;
use JustB2b\Fields\AssociationField;
use JustB2b\Fields\AssociationProductsField;
use JustB2b\Fields\AssociationRolesField;
use JustB2b\Fields\AssociationTermsField;
use JustB2b\Fields\AssociationUsersField;
use JustB2b\Fields\NonNegativeNumberField;
use JustB2b\Fields\NumberField;
use JustB2b\Fields\RichText;
use JustB2b\Fields\SelectField;
use JustB2b\Fields\TextField;
use JustB2b\Traits\RuntimeCacheTrait;

defined('ABSPATH') || exit;

class RuleModel extends AbstractPostModel
{
    use RuntimeCacheTrait;

    protected static string $key = 'rule';
    protected ProductModel $product;

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

    protected function cacheContext(array $extra = []): array
    {
        return array_merge([
            parent::cacheContext($extra),
            'product_id' => $this->product->getId(),
            'qty' => $this->product->getQty()
        ]);
    }

    public function getPriority(): int
    {
        return (int) self::getFromRuntimeCache(
            fn () => $this->getFieldValue('priority'),
            $this->cacheContext()
        );
    }

    public function getKind(): string
    {
        return (string) self::getFromRuntimeCache(
            fn () => $this->getFieldValue('kind'),
            $this->cacheContext()
        );
    }

    public function getPrimaryPriceSource(): string
    {
        return (string) self::getFromRuntimeCache(
            fn () => $this->getFieldValue('primary_price_source'),
            $this->cacheContext()
        );
    }

    public function getSecondaryPriceSource(): string
    {
        return (string) self::getFromRuntimeCache(
            fn () => $this->getFieldValue('secondary_price_source'),
            $this->cacheContext()
        );
    }

    public function getSecondaryRRPSource(): string
    {
        return (string) self::getFromRuntimeCache(
            fn () => $this->getFieldValue('secondary_rrp_source'),
            $this->cacheContext()
        );
    }

    public function getValue(): float
    {
        return self::getFromRuntimeCache(
            fn () => $this->getFieldValue('value'),
            $this->cacheContext()
        );
    }

    public function getMinQty(): int
    {
        return self::getFromRuntimeCache(
            fn () => $this->getFieldValue('min_qty'),
            $this->cacheContext()
        );
    }

    public function getMaxQty(): int
    {
        return self::getFromRuntimeCache(
            fn () => $this->getFieldValue('max_qty'),
            $this->cacheContext()
        );
    }

    public function showInQtyTable(): bool
    {
        return self::getFromRuntimeCache(
            fn () => $this->getFieldValue('show_in_qty_table') !== 'hide',
            $this->cacheContext()
        );
    }

    public function doesQtyFits(): bool
    {
        return self::getFromRuntimeCache(function () {
            $qty = $this->getProduct()->getQty();
            return ($this->getMinQty() === 0 || $this->getMinQty() <= $qty)
                && ($this->getMaxQty() === 0 || $qty <= $this->getMaxQty());
        }, $this->cacheContext());
    }

    public function isAssociationFit(): bool
    {
        return self::getFromRuntimeCache(function () {
            $userController = UsersController::getInstance();
            $currentUser = $userController->getCurrentUser();
            $currentUserId = $currentUser->getId();
            $productId = $this->getProduct()->getId();

            return $this->passesMainUsersRolesCheck($currentUserId)
                && $this->passesMainProductsTermsCheck($productId)
                && $this->passesQualifyingRolesCheck($currentUserId)
                && $this->passesQualifyingTermsCheck($productId)
                && $this->passesExcludingUsersRolesCheck($currentUserId)
                && $this->passesExcludingProductsTermsCheck($productId);
        }, $this->cacheContext());
    }

    public function isPurchasable(): bool
    {
        return $this->getKind() !== 'non_purchasable';
    }


    public function isInLoopHidden(): bool
    {
        return self::getFromRuntimeCache(function () {
            $visibility = $this->getFieldValue('visibility');
            return in_array($visibility, ['loop_hidden', 'fully_hidden'], true);
        }, $this->cacheContext());
    }

    public function isFullyHidden(): bool
    {
        return self::getFromRuntimeCache(
            fn () => $this->getFieldValue('visibility') === 'fully_hidden',
            $this->cacheContext()
        );
    }

    public function isZeroRequestPrice(): bool
    {
        return self::getFromRuntimeCache(
            fn () => $this->getKind() === 'zero_order_for_price',
            $this->cacheContext()
        );
    }

    public function isPricesInLoopHidden(): bool
    {
        return self::getFromRuntimeCache(function () {
            $v = $this->getFieldValue('all_prices_visibility') ?: 'show';
            return in_array($v, ['hide', 'only_product'], true);
        }, $this->cacheContext());
    }

    public function isPricesInProductHidden(): bool
    {
        return self::getFromRuntimeCache(function () {
            $v = $this->getFieldValue('all_prices_visibility') ?: 'show';
            return in_array($v, ['hide', 'only_loop'], true);
        }, $this->cacheContext());
    }


    private function passesMainUsersRolesCheck(int $userId): bool
    {
        var_dump($this->getField("users")->isPostFieldEmpty($this->id));
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

            (new NonNegativeNumberField('min_qty', 'Min ilość'))
                ->setAttribute('type', 'number')
                ->setAttribute('step', 'any')
                ->setHelpText('Min quantity to apply the rule. Defaults to 0.')
                ->setWidth(25),

            (new NonNegativeNumberField('max_qty', 'Max ilość'))
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
