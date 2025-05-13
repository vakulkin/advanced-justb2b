<?php

namespace JustB2b\Models;

use JustB2b\Fields\AbstractField;
use JustB2b\Fields\SelectField;
use JustB2b\Traits\LazyLoaderTrait;
use JustB2b\Utils\Prefixer;

defined('ABSPATH') || exit;

class UserModel extends AbstractIdModel
{
    use LazyLoaderTrait;

    protected ?bool $isB2b = null;

    public function isB2b(): bool
    {
        $this->initIsB2b();
        return $this->isB2b;
    }

    protected function initIsB2b(): void
    {
        $this->lazyLoad($this->isB2b, function () {
            $isB2b = get_user_meta(
                $this->id,
                Prefixer::getPrefixedMeta('kind'),
                true
            );

            return $isB2b === 'b2b';
        });
    }

    public static function getFieldsDefinition(): array
    {
        return [
            new SelectField('kind', 'Rodzaj'),
        ];
    }

    public function getFieldValue(string $key): mixed
    {
        /** @var AbstractField $field */
        $field = $this->getField($key);
        return $field ? $field->getUserFieldValue($this->id) : false;
    }
}
