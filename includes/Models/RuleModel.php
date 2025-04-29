<?php

namespace JustB2b\Models;

use JustB2b\Utils\Prefixer;
use JustB2b\Controllers\UsersController;
use JustB2b\Utils\Pricing\PriceCalculator;
use JustB2b\Traits\LazyLoaderTrait;

defined('ABSPATH') || exit;

class RuleModel extends BasePostModel
{
    use LazyLoaderTrait;

    protected static string $key = 'rule';

    protected int $conditionProductId;
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
    protected ?bool $isQtyFits = null;
    protected ?bool $isAssociationFits = null;

    public function __construct(
        int $id,
        int $conditionProductId,
        int $conditionQty
    ) {
        parent::__construct($id);

        $this->initConditionProductId($conditionProductId);
        $this->initConditionQty($conditionQty);
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
            return carbon_get_post_meta($this->id, Prefixer::getPrefixed('kind'), true) ?: '';
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
            return carbon_get_post_meta($this->id, Prefixer::getPrefixed('primary_price_source'), true) ?: '_price';
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
            return carbon_get_post_meta($this->id, Prefixer::getPrefixed('secondary_primary_price_source'), true) ?: 'disabled';
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
            return carbon_get_post_meta($this->id, Prefixer::getPrefixed('secondary_rrp_source'), true) ?: 'disabled';
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

    public function isQtyFits(): bool
    {
        $this->initQtyFits();
        return $this->isQtyFits;
    }

    protected function initQtyFits(): void
    {
        $this->lazyLoad($this->isQtyFits, function () {
            $minQty = $this->getMinQty();
            $maxQty = $this->getMaxQty();
            $qty = $this->getConditionQty();
            return ($minQty === 0 || $minQty <= $qty) && ($maxQty === 0 || $qty <= $maxQty);
        });
    }

    public function isAssociationFits(): bool
    {
        $this->initAssociationFits();
        return $this->isAssociationFits;
    }

    protected function initAssociationFits(): void
    {
        $this->lazyLoad($this->isAssociationFits, function () {
            $userController = UsersController::getInstance();
            $currentUser = $userController->getCurrentUser();
            $currentUserId = $currentUser->getId();
            $productId = $this->getConditionProductId();

            if (!$this->passesMainRolesCheck($currentUserId)) {
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

            if (!$this->passesExcludingRolesCheck($currentUserId)) {
                return false;
            }

            if (!$this->passesExcludingProductsTermsCheck($productId)) {
                return false;
            }

            return true;
        });
    }

    private function passesMainRolesCheck(int $userId): bool
    {
        $mainRoles = self::getAssociatedPosts($this->id, Prefixer::getPrefixed('roles'));
        return $this->checkRoles($mainRoles, $userId);
    }

    private function passesMainProductsTermsCheck(int $productId): bool
    {
        $mainProducts = self::getAssociatedPosts($this->id, Prefixer::getPrefixed('products'));
        $mainTerms = self::getAssociatedTerms($this->id, Prefixer::getPrefixed('woo_terms'));
        return $this->checkProduct($mainProducts, $productId)
            || $this->checkTerms($mainTerms, $productId);
    }

    private function passesQualifyingRolesCheck(int $userId): bool
    {
        $qualifyingRoles = self::getAssociatedPosts($this->id, Prefixer::getPrefixed('qualifying_roles'));
        return $this->checkRoles($qualifyingRoles, $userId);
    }

    private function passesQualifyingTermsCheck(int $productId): bool
    {
        $qualifyingTerms = self::getAssociatedTerms($this->id, Prefixer::getPrefixed('qualifying_woo_terms'));
        return $this->checkTerms($qualifyingTerms, $productId);
    }

    private function passesExcludingRolesCheck(int $userId): bool
    {
        $excludingRoles = self::getAssociatedPosts($this->id, Prefixer::getPrefixed('excluding_roles'));
        return $this->checkRoles($excludingRoles, $userId, true);
    }

    private function passesExcludingProductsTermsCheck(int $productId): bool
    {
        $excludingProducts = self::getAssociatedPosts($this->id, Prefixer::getPrefixed('products'));
        $excludingTerms = self::getAssociatedTerms($this->id, Prefixer::getPrefixed('woo_terms'));

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
            $users = self::getAssociatedUsers($roleId, Prefixer::getPrefixed('users'));
            if (isset($users[$userId])) {
                $result = true;
                break;
            }
        }
        return $excludingLogic ? !$result : $result;
    }

    public function getConditionProductId(): int
    {
        return $this->conditionProductId;
    }

    protected function initConditionProductId(?int $conditionProductId = null): void
    {
        $this->conditionProductId = $conditionProductId;
    }

    public function getConditionQty(): int
    {
        return $this->conditionQty;
    }

    protected function initConditionQty(?int $conditionQty = null): void
    {
        $this->conditionQty = $conditionQty;
    }
}
