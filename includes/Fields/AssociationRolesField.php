<?php

namespace JustB2b\Fields;

use JustB2b\Utils\Prefixer;

defined('ABSPATH') || exit;

class AssociationRolesField extends AssociationPostsField
{
    public function __construct(string $key, string $label)
    {
        parent::__construct($key, $label);

        $this->setTypes([
            [
                'type' => 'post',
                'post_type' => Prefixer::getPrefixed('role'),
            ],
        ]);
    }
}
