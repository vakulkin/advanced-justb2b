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

        /** @var AbstractField $fiedefinitionld */
        foreach ($definitions as $definition) {
            $fields[] = $definition->toCarbonField();
        }

        return $fields;
    }
}
