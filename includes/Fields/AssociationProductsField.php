<?php

namespace JustB2b\Fields;

defined('ABSPATH') || exit;

class AssociationProductsField extends AssociationPostsField
{
    public function __construct(string $key, string $label)
    {
        parent::__construct($key, $label);

        $this->setTypes([
            [
                'type' => 'post',
                'post_type' => 'product',
            ],
            // Uncomment if variations should be selectable:
            // [
            //     'type' => 'post',
            //     'post_type' => 'product_variation',
            // ],
        ]);
    }
}
