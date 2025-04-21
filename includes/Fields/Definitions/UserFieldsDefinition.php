<?php

namespace JustB2b\Fields\Definitions;



defined('ABSPATH') || exit;

use JustB2b\Fields\SelectField;

class UserFieldsDefinition
{
    public static function getMainFileds(): array
    {
        return [
            (new SelectField('kind', 'Rodzaj'))
                ->setOptions([
                    'b2c' => 'b2c',
                    'b2b' => 'b2b',
                ])
        ];
    }

}
