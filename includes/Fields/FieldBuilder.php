<?php

namespace JustB2b\Fields;

defined('ABSPATH') || exit;

use Carbon_Fields\Field\Field;

class FieldBuilder
{
    /**
     * @param AbstractField[] $definitions
     * @return Field[]
     */
    public static function buildFields(array $definitions): array
    {
        $fields = [];

        foreach ($definitions as $definition) {
            if ($definition instanceof AbstractField) {
                $fields[] = $definition->toCarbonField();
            }
        }

        return $fields;
    }
}
