<?php

namespace JustB2b\Fields;

defined('ABSPATH') || exit;

class FieldBuilder
{
    public static function buildFields(array $definitions): array
    {
        $fields = [];

        foreach ($definitions as $definition) {
            if ($definition instanceof BaseField) {
                $fields[] = $definition->toCarbonField();
            }
        }

        return $fields;
    }
}
