<?php

namespace JustB2b\Models;

use JustB2b\Traits\LazyLoaderTrait;
use JustB2b\Utils\Prefixer;

defined('ABSPATH') || exit;

class UserModel extends BaseModel
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
}
