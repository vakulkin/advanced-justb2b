<?php

namespace JustB2b\Models\Key;

defined('ABSPATH') || exit;


class CheckoutModel extends AbstractKeyModel
{
    public function getKey(): string
    {
        return 'checkout';
    }

    public static function getFieldsDefinition(): array
    {
        return [];
    }
}
