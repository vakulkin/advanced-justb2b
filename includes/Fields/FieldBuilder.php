<?php

namespace JustB2b\Fields;

defined('ABSPATH') || exit;

use Carbon_Fields\Field\Field;

class FieldBuilder
{
    /**
     * @param BaseField[] $definitions
     * @return Field[]
     */
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
