<?php

namespace JustB2b\Models;

use JustB2b\Fields\AssociationProductsField;
use JustB2b\Fields\AssociationRolesField;
use JustB2b\Fields\AssociationTermsField;
use JustB2b\Fields\AssociationUsersField;
use JustB2b\Utils\Prefixer;
use JustB2b\Controllers\UsersController;
use JustB2b\Utils\Pricing\PriceCalculator;
use JustB2b\Traits\LazyLoaderTrait;

defined('ABSPATH') || exit;

class RuleModel extends BasePostModel
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

    public function __construct(
        int $id,
        ProductModel $product
    ) {
        parent::__construct($id);
        $this->initProduct($product);
    }

    public static function getSingleName(): string {
        return __('Rule', 'justb2b');
    }
    public static function getPluralName(): string {
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
            return (int) carbon_get_post_meta($this->id, Prefixer::getPrefixed('priority'));
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
            return carbon_get_post_meta($this->id, Prefixer::getPrefixed('kind')) ?: '';
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
            return carbon_get_post_meta($this->id, Prefixer::getPrefixed('primary_price_source')) ?: '_price';
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
            return carbon_get_post_meta($this->id, Prefixer::getPrefixed('secondary_price_source')) ?: 'disabled';
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
            return carbon_get_post_meta($this->id, Prefixer::getPrefixed('secondary_rrp_source')) ?: 'disabled';
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
            $value = carbon_get_post_meta($this->id, Prefixer::getPrefixed('value'));
            return PriceCalculator::getFloat($value);
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
            $minQty = carbon_get_post_meta($this->id, Prefixer::getPrefixed('min_qty'));
            return abs((int) $minQty);
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
            $maxQty = carbon_get_post_meta($this->id, Prefixer::getPrefixed('max_qty'));
            return abs((int) $maxQty);
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
            $show = carbon_get_post_meta($this->id, Prefixer::getPrefixed('show_in_qty_table'));
            return $show !== 'hide';
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
            $minQty = $this->getMinQty();
            $maxQty = $this->getMaxQty();
            $qty = $product->getQty();

            return ($minQty === 0 || $minQty <= $qty) && ($maxQty === 0 || $qty <= $maxQty);
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
        $users = AssociationUsersField::getValues($this->id, Prefixer::getPrefixed('users'));
        $roles = AssociationRolesField::getValues($this->id, Prefixer::getPrefixed('roles'));

        $hasUsers = !empty($users);
        $hasRoles = !empty($roles);

        // If neither users nor roles are defined, allow by default
        if (!$hasUsers && !$hasRoles) {
            return true;
        }

        // If any defined condition fails, return false
        if ($hasUsers && !$this->checkUsers($users, $userId)) {
            return false;
        }

        if ($hasRoles && !$this->checkRoles($roles, $userId)) {
            return false;
        }

        return true;
    }

    private function passesMainProductsTermsCheck(int $productId): bool
    {
        $products = AssociationProductsField::getValues($this->id, Prefixer::getPrefixed('products'));
        $terms = AssociationTermsField::getValues($this->id, Prefixer::getPrefixed('woo_terms'));

        $hasProducts = !empty($products);
        $hasTerms = !empty($terms);

        if (!$hasProducts && !$hasTerms) {
            return true;
        }

        if ($hasProducts && !$this->checkProduct($products, $productId)) {
            return false;
        }

        if ($hasTerms && !$this->checkTerms($terms, $productId)) {
            return false;
        }

        return true;
    }

    private function passesQualifyingRolesCheck(int $userId): bool
    {
        $qualifyingRoles = AssociationRolesField::getValues($this->id, Prefixer::getPrefixed('qualifying_roles'));
        return $this->checkRoles($qualifyingRoles, $userId);
    }

    private function passesQualifyingTermsCheck(int $productId): bool
    {
        $qualifyingTerms = AssociationTermsField::getValues($this->id, Prefixer::getPrefixed('qualifying_woo_terms'));
        return $this->checkTerms($qualifyingTerms, $productId);
    }

    private function passesExcludingUsersRolesCheck(int $userId): bool
    {
        $excludingUsers = AssociationUsersField::getValues($this->id, Prefixer::getPrefixed('excluding_users'));
        if ($this->checkUsers($excludingUsers, $userId)) {
            return true;
        }

        $excludingRoles = AssociationRolesField::getValues($this->id, Prefixer::getPrefixed('excluding_roles'));
        return $this->checkRoles($excludingRoles, $userId, true);
    }

    private function passesExcludingProductsTermsCheck(int $productId): bool
    {
        $excludingProducts = AssociationProductsField::getValues($this->id, Prefixer::getPrefixed('excluding_products'));
        $excludingTerms = AssociationTermsField::getValues($this->id, Prefixer::getPrefixed('excluding_woo_terms'));

        return $this->checkProduct($excludingProducts, $productId, true)
            || $this->checkTerms($excludingTerms, $productId, true);
    }

    protected function checkProduct(false|array $products, int $productId, bool $excludingLogic = false): bool
    {
        if (false === $products) {
            return false;
        }

        if (empty($products)) {
            return true;
        }

        $result = isset($products[$productId]);
        return $excludingLogic ? !$result : $result;
    }

    protected function checkTerms(false|array $terms, int $productId, bool $excludingLogic = false): bool
    {
        if (false === $terms) {
            return false;
        }

        if (empty($terms)) {
            return true;
        }

        $result = false;
        foreach ($terms as $term) {
            if (has_term($term['id'], $term['taxonomy'], $productId)) {
                $result = true;
                break;
            }
        }

        return $excludingLogic ? !$result : $result;
    }

    protected function checkUsers(false|array $users, int $userId, bool $excludingLogic = false): bool
    {
        if (false === $users) {
            return false;
        }

        if (empty($users)) {
            return true;
        }

        foreach ($users as $user) {
            if ($user['id'] === $userId) {
                return $excludingLogic ? false : true;
            }
        }
        return false;
    }

    protected function checkRoles(false|array $roles, int $userId, bool $excludingLogic = false): bool
    {
        if (false === $roles) {
            return false;
        }

        if (empty($roles)) {
            return true;
        }

        $result = false;
        foreach ($roles as $role) {
            $roleId = $role['id'];
            $users = AssociationUsersField::getValues($roleId, Prefixer::getPrefixed('users'));
            if (isset($users[$userId])) {
                $result = true;
                break;
            }
        }
        return $excludingLogic ? !$result : $result;
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
        return in_array(carbon_get_post_meta($this->id, Prefixer::getPrefixed('visibility')), ['loop_hidden', 'fully_hidden'], true);
    }

    public function isFullyHidden(): bool
    {

        $this->lazyLoad($this->isFullyHidden, [$this, 'initIsFullyHidden']);
        return $this->isFullyHidden;
    }

    protected function initIsFullyHidden(): bool
    {
        return carbon_get_post_meta($this->id, Prefixer::getPrefixed('visibility')) === 'fully_hidden';
    }

    public function isZeroRequestPrice(): bool
    {
        $this->lazyLoad($this->isZeroRequestPrice, [$this, 'initisZeroRequestPrice']);
        return $this->isZeroRequestPrice;
    }

    protected function initisZeroRequestPrice(): bool
    {
        return $this->getKind() === 'zero_order_for_price';
    }

}