<?php

namespace JustB2b\Fields;

defined('ABSPATH') || exit;

use JustB2b\Fields\AbstractField;

class TextField extends AbstractField
{
    protected string $type = 'text';

    protected function isEmpty($value): bool
    {
        return parent::isEmpty($value) || $value === '';
    }
}
