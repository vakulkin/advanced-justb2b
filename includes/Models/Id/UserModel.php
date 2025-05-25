<?php

namespace JustB2b\Models\Id;

use JustB2b\Fields\AbstractField;
use JustB2b\Fields\SelectField;
use JustB2b\Traits\RuntimeCacheTrait;

defined('ABSPATH') || exit;

/**
 * @feature-section user_context
 * @title[ru] Контекст клиента: B2B или B2C
 * @desc[ru] JustB2B автоматически различает клиентов по типу (B2B или B2C) и использует это как основу для ценообразования и отображения товаров.
 * @order 150
 */

/**
 * @feature user_context model
 * @title[ru] Идентификация клиента
 * @desc[ru] Плагин определяет, является ли пользователь B2B-клиентом, и на основе этого показывает нужные цены и товары.
 * @order 151
 */


class UserModel extends AbstractIdModel
{
    use RuntimeCacheTrait;

    protected function cacheContext(array $extra = []): array
    {
        return array_merge(['user_id' => $this->id], $extra);
    }

    /**
     * @feature user_context is_b2b
     * @title[ru] Определение B2B-клиента
     * @desc[ru] Система понимает, когда пользователь относится к сегменту B2B, и применяет соответствующие правила и цены.
     * @order 152
     */
    public function isB2b(): bool
    {
        return self::getFromRuntimeCache(function () {
            $kind = $this->getFieldValue('kind');
            return $kind === 'b2b';
        }, $this->cacheContext());

    }

    public static function getFieldsDefinition(): array
    {
        return [
            (new SelectField('kind', 'Rodzaj'))
                ->setOptions([
                    'b2c' => 'b2c',
                    'b2b' => 'b2b',
                ]),
        ];
    }

    public function isEmptyField($key): bool
    {
        /** @var AbstractField $field */
        $field = $this->getField($key);
        return $field ? $field->isUserFieldEmpty($this->id) : true;
    }

    public function getFieldValue(string $key): mixed
    {
        /** @var AbstractField $field */
        $field = $this->getField($key);
        return $field ? $field->getUserFieldValue($this->id) : null;
    }
}
