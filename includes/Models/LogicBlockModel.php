<?php

namespace JustB2b\Models;

use JustB2b\Utils\Prefixer;

defined('ABSPATH') || exit;

class LogicBlockModel extends BaseModel
{
    protected bool $isFits = false;
    protected static string $key = 'logic-block';

    public function __construct(int $id, int $conditionProductId = null, int $conditionUserId = null)
    {
        parent::__construct($id);
        $this->initFit($conditionProductId, $conditionUserId);
    }

    protected function initFit(int $conditionProductId = null, int $conditionUserId = null)
    {
        $this->isFits = $this->checkRoles($conditionUserId) &&
            ($this->checkProduct($conditionProductId)
                || $this->checkTerms($conditionProductId));
    }

    public function isFits(): bool
    {
        return $this->isFits;
    }


    protected function checkProduct(int $productId = null): bool
    {
        $products = self::getAssociatedPosts($this->id, Prefixer::getPrefixed('products'));
        return isset($products[$productId]);
    }

    protected function checkTerms(int $productId = null)
    {
        $terms = self::getAssociatedTerms($this->id, Prefixer::getPrefixed('woo_terms'));
        foreach ($terms as $term) {
            if (has_term($term['id'], $term['taxonomy'], $productId)) {
                return true;
            }
        }
        return false;
    }

    protected function checkRoles(int $userId = null): bool
    {
        return true; // TODO: Implement checkRoles() method.
    }

}