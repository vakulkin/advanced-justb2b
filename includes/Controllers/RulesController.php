<?php

namespace JustB2b\Controllers;


defined('ABSPATH') || exit;

use Carbon_Fields\Container;
use JustB2b\Models\RuleModel;
use JustB2b\Fields\FieldBuilder;
use JustB2b\Fields\Definitions\RulesFieldsDefinition;


class RulesController extends BaseCustomPostController
{
    protected static string $modelClass = RuleModel::class;

    public function registerFields()
    {
        $definitions = RulesFieldsDefinition::getMainFileds();
        $fields = FieldBuilder::buildFields($definitions);

        Container::make('post_meta', 'JustB2B')
            ->where('post_type', '=', self::$modelClass::getPrefixedKey())
            ->set_context('side')
            ->set_priority('default')
            ->add_fields($fields);

        $definitions = RulesFieldsDefinition::getMainConditions();
        $fields = FieldBuilder::buildFields($definitions);

        Container::make('post_meta', 'Main Conditions')
            ->where('post_type', '=', self::$modelClass::getPrefixedKey())
            ->add_fields($fields);

        $definitions = RulesFieldsDefinition::getGualifyingConditions();
        $fields = FieldBuilder::buildFields(definitions: $definitions);

        Container::make('post_meta', 'Qualifying Conditions')
            ->where('post_type', '=', self::$modelClass::getPrefixedKey())
            ->add_fields($fields);

        $definitions = RulesFieldsDefinition::getExcludingConditions();
        $fields = FieldBuilder::buildFields($definitions);

        Container::make('post_meta', 'Excluding Conditions')
            ->where('post_type', '=', self::$modelClass::getPrefixedKey())
            ->add_fields($fields);
    }
}