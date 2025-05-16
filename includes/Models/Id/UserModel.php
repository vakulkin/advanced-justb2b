<?php

namespace JustB2b\Models\Id;

use JustB2b\Fields\AbstractField;
use JustB2b\Fields\SelectField;
use JustB2b\Traits\RuntimeCacheTrait;

defined('ABSPATH') || exit;

class UserModel extends AbstractIdModel
{
    use RuntimeCacheTrait;

    protected function cacheContext(array $extra = []): array
    {
        return array_merge(['user_id' => $this->id], $extra);
    }

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
