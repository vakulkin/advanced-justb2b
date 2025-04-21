<?php

namespace JustB2b\Models;

defined('ABSPATH') || exit;

use JustB2b\Utils\Prefixer;

class UserModel extends BaseModel
{
    protected bool $isB2b;

    public function __construct(int $id) {
        parent::__construct($id);
        $this->initIsB2b();
    }

    public function isB2b() {
        return $this->isB2b;
    }

    protected function initIsB2b() {

        $isB2b = get_user_meta(
            $this->id,
            Prefixer::getPrefixedMeta('kind'),
            true
        );
        
        $this->isB2b = $isB2b === 'b2b';
    }
}
