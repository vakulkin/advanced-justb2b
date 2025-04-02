<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit8392046e6f3fcbecefee487ba1a61925
{
    public static $prefixLengthsPsr4 = array (
        'J' => 
        array (
            'JustB2b\\' => 8,
        ),
        'C' => 
        array (
            'Carbon_Fields\\' => 14,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'JustB2b\\' => 
        array (
            0 => __DIR__ . '/../..' . '/includes',
        ),
        'Carbon_Fields\\' => 
        array (
            0 => __DIR__ . '/..' . '/htmlburger/carbon-fields/core',
        ),
    );

    public static $classMap = array (
        'Carbon_Fields\\Block' => __DIR__ . '/..' . '/htmlburger/carbon-fields/core/Block.php',
        'Carbon_Fields\\Carbon_Fields' => __DIR__ . '/..' . '/htmlburger/carbon-fields/core/Carbon_Fields.php',
        'Carbon_Fields\\Container' => __DIR__ . '/..' . '/htmlburger/carbon-fields/core/Container.php',
        'Carbon_Fields\\Container\\Block_Container' => __DIR__ . '/..' . '/htmlburger/carbon-fields/core/Container/Block_Container.php',
        'Carbon_Fields\\Container\\Broken_Container' => __DIR__ . '/..' . '/htmlburger/carbon-fields/core/Container/Broken_Container.php',
        'Carbon_Fields\\Container\\Comment_Meta_Container' => __DIR__ . '/..' . '/htmlburger/carbon-fields/core/Container/Comment_Meta_Container.php',
        'Carbon_Fields\\Container\\Condition\\Blog_ID_Condition' => __DIR__ . '/..' . '/htmlburger/carbon-fields/core/Container/Condition/Blog_ID_Condition.php',
        'Carbon_Fields\\Container\\Condition\\Boolean_Condition' => __DIR__ . '/..' . '/htmlburger/carbon-fields/core/Container/Condition/Boolean_Condition.php',
        'Carbon_Fields\\Container\\Condition\\Comparer\\Any_Contain_Comparer' => __DIR__ . '/..' . '/htmlburger/carbon-fields/core/Container/Condition/Comparer/Any_Contain_Comparer.php',
        'Carbon_Fields\\Container\\Condition\\Comparer\\Any_Equality_Comparer' => __DIR__ . '/..' . '/htmlburger/carbon-fields/core/Container/Condition/Comparer/Any_Equality_Comparer.php',
        'Carbon_Fields\\Container\\Condition\\Comparer\\Comparer' => __DIR__ . '/..' . '/htmlburger/carbon-fields/core/Container/Condition/Comparer/Comparer.php',
        'Carbon_Fields\\Container\\Condition\\Comparer\\Contain_Comparer' => __DIR__ . '/..' . '/htmlburger/carbon-fields/core/Container/Condition/Comparer/Contain_Comparer.php',
        'Carbon_Fields\\Container\\Condition\\Comparer\\Custom_Comparer' => __DIR__ . '/..' . '/htmlburger/carbon-fields/core/Container/Condition/Comparer/Custom_Comparer.php',
        'Carbon_Fields\\Container\\Condition\\Comparer\\Equality_Comparer' => __DIR__ . '/..' . '/htmlburger/carbon-fields/core/Container/Condition/Comparer/Equality_Comparer.php',
        'Carbon_Fields\\Container\\Condition\\Comparer\\Scalar_Comparer' => __DIR__ . '/..' . '/htmlburger/carbon-fields/core/Container/Condition/Comparer/Scalar_Comparer.php',
        'Carbon_Fields\\Container\\Condition\\Condition' => __DIR__ . '/..' . '/htmlburger/carbon-fields/core/Container/Condition/Condition.php',
        'Carbon_Fields\\Container\\Condition\\Current_User_Capability_Condition' => __DIR__ . '/..' . '/htmlburger/carbon-fields/core/Container/Condition/Current_User_Capability_Condition.php',
        'Carbon_Fields\\Container\\Condition\\Current_User_ID_Condition' => __DIR__ . '/..' . '/htmlburger/carbon-fields/core/Container/Condition/Current_User_ID_Condition.php',
        'Carbon_Fields\\Container\\Condition\\Current_User_Role_Condition' => __DIR__ . '/..' . '/htmlburger/carbon-fields/core/Container/Condition/Current_User_Role_Condition.php',
        'Carbon_Fields\\Container\\Condition\\Factory' => __DIR__ . '/..' . '/htmlburger/carbon-fields/core/Container/Condition/Factory.php',
        'Carbon_Fields\\Container\\Condition\\Post_Ancestor_ID_Condition' => __DIR__ . '/..' . '/htmlburger/carbon-fields/core/Container/Condition/Post_Ancestor_ID_Condition.php',
        'Carbon_Fields\\Container\\Condition\\Post_Format_Condition' => __DIR__ . '/..' . '/htmlburger/carbon-fields/core/Container/Condition/Post_Format_Condition.php',
        'Carbon_Fields\\Container\\Condition\\Post_ID_Condition' => __DIR__ . '/..' . '/htmlburger/carbon-fields/core/Container/Condition/Post_ID_Condition.php',
        'Carbon_Fields\\Container\\Condition\\Post_Level_Condition' => __DIR__ . '/..' . '/htmlburger/carbon-fields/core/Container/Condition/Post_Level_Condition.php',
        'Carbon_Fields\\Container\\Condition\\Post_Parent_ID_Condition' => __DIR__ . '/..' . '/htmlburger/carbon-fields/core/Container/Condition/Post_Parent_ID_Condition.php',
        'Carbon_Fields\\Container\\Condition\\Post_Template_Condition' => __DIR__ . '/..' . '/htmlburger/carbon-fields/core/Container/Condition/Post_Template_Condition.php',
        'Carbon_Fields\\Container\\Condition\\Post_Term_Condition' => __DIR__ . '/..' . '/htmlburger/carbon-fields/core/Container/Condition/Post_Term_Condition.php',
        'Carbon_Fields\\Container\\Condition\\Post_Type_Condition' => __DIR__ . '/..' . '/htmlburger/carbon-fields/core/Container/Condition/Post_Type_Condition.php',
        'Carbon_Fields\\Container\\Condition\\Term_Ancestor_Condition' => __DIR__ . '/..' . '/htmlburger/carbon-fields/core/Container/Condition/Term_Ancestor_Condition.php',
        'Carbon_Fields\\Container\\Condition\\Term_Condition' => __DIR__ . '/..' . '/htmlburger/carbon-fields/core/Container/Condition/Term_Condition.php',
        'Carbon_Fields\\Container\\Condition\\Term_Level_Condition' => __DIR__ . '/..' . '/htmlburger/carbon-fields/core/Container/Condition/Term_Level_Condition.php',
        'Carbon_Fields\\Container\\Condition\\Term_Parent_Condition' => __DIR__ . '/..' . '/htmlburger/carbon-fields/core/Container/Condition/Term_Parent_Condition.php',
        'Carbon_Fields\\Container\\Condition\\Term_Taxonomy_Condition' => __DIR__ . '/..' . '/htmlburger/carbon-fields/core/Container/Condition/Term_Taxonomy_Condition.php',
        'Carbon_Fields\\Container\\Condition\\User_Capability_Condition' => __DIR__ . '/..' . '/htmlburger/carbon-fields/core/Container/Condition/User_Capability_Condition.php',
        'Carbon_Fields\\Container\\Condition\\User_ID_Condition' => __DIR__ . '/..' . '/htmlburger/carbon-fields/core/Container/Condition/User_ID_Condition.php',
        'Carbon_Fields\\Container\\Condition\\User_Role_Condition' => __DIR__ . '/..' . '/htmlburger/carbon-fields/core/Container/Condition/User_Role_Condition.php',
        'Carbon_Fields\\Container\\Container' => __DIR__ . '/..' . '/htmlburger/carbon-fields/core/Container/Container.php',
        'Carbon_Fields\\Container\\Fulfillable\\Fulfillable' => __DIR__ . '/..' . '/htmlburger/carbon-fields/core/Container/Fulfillable/Fulfillable.php',
        'Carbon_Fields\\Container\\Fulfillable\\Fulfillable_Collection' => __DIR__ . '/..' . '/htmlburger/carbon-fields/core/Container/Fulfillable/Fulfillable_Collection.php',
        'Carbon_Fields\\Container\\Fulfillable\\Translator\\Array_Translator' => __DIR__ . '/..' . '/htmlburger/carbon-fields/core/Container/Fulfillable/Translator/Array_Translator.php',
        'Carbon_Fields\\Container\\Fulfillable\\Translator\\Json_Translator' => __DIR__ . '/..' . '/htmlburger/carbon-fields/core/Container/Fulfillable/Translator/Json_Translator.php',
        'Carbon_Fields\\Container\\Fulfillable\\Translator\\Translator' => __DIR__ . '/..' . '/htmlburger/carbon-fields/core/Container/Fulfillable/Translator/Translator.php',
        'Carbon_Fields\\Container\\Nav_Menu_Item_Container' => __DIR__ . '/..' . '/htmlburger/carbon-fields/core/Container/Nav_Menu_Item_Container.php',
        'Carbon_Fields\\Container\\Network_Container' => __DIR__ . '/..' . '/htmlburger/carbon-fields/core/Container/Network_Container.php',
        'Carbon_Fields\\Container\\Post_Meta_Container' => __DIR__ . '/..' . '/htmlburger/carbon-fields/core/Container/Post_Meta_Container.php',
        'Carbon_Fields\\Container\\Repository' => __DIR__ . '/..' . '/htmlburger/carbon-fields/core/Container/Repository.php',
        'Carbon_Fields\\Container\\Term_Meta_Container' => __DIR__ . '/..' . '/htmlburger/carbon-fields/core/Container/Term_Meta_Container.php',
        'Carbon_Fields\\Container\\Theme_Options_Container' => __DIR__ . '/..' . '/htmlburger/carbon-fields/core/Container/Theme_Options_Container.php',
        'Carbon_Fields\\Container\\User_Meta_Container' => __DIR__ . '/..' . '/htmlburger/carbon-fields/core/Container/User_Meta_Container.php',
        'Carbon_Fields\\Container\\Widget_Container' => __DIR__ . '/..' . '/htmlburger/carbon-fields/core/Container/Widget_Container.php',
        'Carbon_Fields\\Datastore\\Comment_Meta_Datastore' => __DIR__ . '/..' . '/htmlburger/carbon-fields/core/Datastore/Comment_Meta_Datastore.php',
        'Carbon_Fields\\Datastore\\Datastore' => __DIR__ . '/..' . '/htmlburger/carbon-fields/core/Datastore/Datastore.php',
        'Carbon_Fields\\Datastore\\Datastore_Holder_Interface' => __DIR__ . '/..' . '/htmlburger/carbon-fields/core/Datastore/Datastore_Holder_Interface.php',
        'Carbon_Fields\\Datastore\\Datastore_Interface' => __DIR__ . '/..' . '/htmlburger/carbon-fields/core/Datastore/Datastore_Interface.php',
        'Carbon_Fields\\Datastore\\Empty_Datastore' => __DIR__ . '/..' . '/htmlburger/carbon-fields/core/Datastore/Empty_Datastore.php',
        'Carbon_Fields\\Datastore\\Key_Value_Datastore' => __DIR__ . '/..' . '/htmlburger/carbon-fields/core/Datastore/Key_Value_Datastore.php',
        'Carbon_Fields\\Datastore\\Meta_Datastore' => __DIR__ . '/..' . '/htmlburger/carbon-fields/core/Datastore/Meta_Datastore.php',
        'Carbon_Fields\\Datastore\\Nav_Menu_Item_Datastore' => __DIR__ . '/..' . '/htmlburger/carbon-fields/core/Datastore/Nav_Menu_Item_Datastore.php',
        'Carbon_Fields\\Datastore\\Network_Datastore' => __DIR__ . '/..' . '/htmlburger/carbon-fields/core/Datastore/Network_Datastore.php',
        'Carbon_Fields\\Datastore\\Post_Meta_Datastore' => __DIR__ . '/..' . '/htmlburger/carbon-fields/core/Datastore/Post_Meta_Datastore.php',
        'Carbon_Fields\\Datastore\\Term_Meta_Datastore' => __DIR__ . '/..' . '/htmlburger/carbon-fields/core/Datastore/Term_Meta_Datastore.php',
        'Carbon_Fields\\Datastore\\Theme_Options_Datastore' => __DIR__ . '/..' . '/htmlburger/carbon-fields/core/Datastore/Theme_Options_Datastore.php',
        'Carbon_Fields\\Datastore\\User_Meta_Datastore' => __DIR__ . '/..' . '/htmlburger/carbon-fields/core/Datastore/User_Meta_Datastore.php',
        'Carbon_Fields\\Datastore\\Widget_Datastore' => __DIR__ . '/..' . '/htmlburger/carbon-fields/core/Datastore/Widget_Datastore.php',
        'Carbon_Fields\\Event\\Emitter' => __DIR__ . '/..' . '/htmlburger/carbon-fields/core/Event/Emitter.php',
        'Carbon_Fields\\Event\\Listener' => __DIR__ . '/..' . '/htmlburger/carbon-fields/core/Event/Listener.php',
        'Carbon_Fields\\Event\\PersistentListener' => __DIR__ . '/..' . '/htmlburger/carbon-fields/core/Event/PersistentListener.php',
        'Carbon_Fields\\Event\\SingleEventListener' => __DIR__ . '/..' . '/htmlburger/carbon-fields/core/Event/SingleEventListener.php',
        'Carbon_Fields\\Exception\\Incorrect_Syntax_Exception' => __DIR__ . '/..' . '/htmlburger/carbon-fields/core/Exception/Incorrect_Syntax_Exception.php',
        'Carbon_Fields\\Field' => __DIR__ . '/..' . '/htmlburger/carbon-fields/core/Field.php',
        'Carbon_Fields\\Field\\Association_Field' => __DIR__ . '/..' . '/htmlburger/carbon-fields/core/Field/Association_Field.php',
        'Carbon_Fields\\Field\\Block_Preview_Field' => __DIR__ . '/..' . '/htmlburger/carbon-fields/core/Field/Block_Preview_Field.php',
        'Carbon_Fields\\Field\\Broken_Field' => __DIR__ . '/..' . '/htmlburger/carbon-fields/core/Field/Broken_Field.php',
        'Carbon_Fields\\Field\\Checkbox_Field' => __DIR__ . '/..' . '/htmlburger/carbon-fields/core/Field/Checkbox_Field.php',
        'Carbon_Fields\\Field\\Color_Field' => __DIR__ . '/..' . '/htmlburger/carbon-fields/core/Field/Color_Field.php',
        'Carbon_Fields\\Field\\Complex_Field' => __DIR__ . '/..' . '/htmlburger/carbon-fields/core/Field/Complex_Field.php',
        'Carbon_Fields\\Field\\Date_Field' => __DIR__ . '/..' . '/htmlburger/carbon-fields/core/Field/Date_Field.php',
        'Carbon_Fields\\Field\\Date_Time_Field' => __DIR__ . '/..' . '/htmlburger/carbon-fields/core/Field/Date_Time_Field.php',
        'Carbon_Fields\\Field\\Field' => __DIR__ . '/..' . '/htmlburger/carbon-fields/core/Field/Field.php',
        'Carbon_Fields\\Field\\File_Field' => __DIR__ . '/..' . '/htmlburger/carbon-fields/core/Field/File_Field.php',
        'Carbon_Fields\\Field\\Footer_Scripts_Field' => __DIR__ . '/..' . '/htmlburger/carbon-fields/core/Field/Footer_Scripts_Field.php',
        'Carbon_Fields\\Field\\Gravity_Form_Field' => __DIR__ . '/..' . '/htmlburger/carbon-fields/core/Field/Gravity_Form_Field.php',
        'Carbon_Fields\\Field\\Group_Field' => __DIR__ . '/..' . '/htmlburger/carbon-fields/core/Field/Group_Field.php',
        'Carbon_Fields\\Field\\Header_Scripts_Field' => __DIR__ . '/..' . '/htmlburger/carbon-fields/core/Field/Header_Scripts_Field.php',
        'Carbon_Fields\\Field\\Hidden_Field' => __DIR__ . '/..' . '/htmlburger/carbon-fields/core/Field/Hidden_Field.php',
        'Carbon_Fields\\Field\\Html_Field' => __DIR__ . '/..' . '/htmlburger/carbon-fields/core/Field/Html_Field.php',
        'Carbon_Fields\\Field\\Image_Field' => __DIR__ . '/..' . '/htmlburger/carbon-fields/core/Field/Image_Field.php',
        'Carbon_Fields\\Field\\Map_Field' => __DIR__ . '/..' . '/htmlburger/carbon-fields/core/Field/Map_Field.php',
        'Carbon_Fields\\Field\\Media_Gallery_Field' => __DIR__ . '/..' . '/htmlburger/carbon-fields/core/Field/Media_Gallery_Field.php',
        'Carbon_Fields\\Field\\Multiselect_Field' => __DIR__ . '/..' . '/htmlburger/carbon-fields/core/Field/Multiselect_Field.php',
        'Carbon_Fields\\Field\\Oembed_Field' => __DIR__ . '/..' . '/htmlburger/carbon-fields/core/Field/Oembed_Field.php',
        'Carbon_Fields\\Field\\Predefined_Options_Field' => __DIR__ . '/..' . '/htmlburger/carbon-fields/core/Field/Predefined_Options_Field.php',
        'Carbon_Fields\\Field\\Radio_Field' => __DIR__ . '/..' . '/htmlburger/carbon-fields/core/Field/Radio_Field.php',
        'Carbon_Fields\\Field\\Radio_Image_Field' => __DIR__ . '/..' . '/htmlburger/carbon-fields/core/Field/Radio_Image_Field.php',
        'Carbon_Fields\\Field\\Rich_Text_Field' => __DIR__ . '/..' . '/htmlburger/carbon-fields/core/Field/Rich_Text_Field.php',
        'Carbon_Fields\\Field\\Scripts_Field' => __DIR__ . '/..' . '/htmlburger/carbon-fields/core/Field/Scripts_Field.php',
        'Carbon_Fields\\Field\\Select_Field' => __DIR__ . '/..' . '/htmlburger/carbon-fields/core/Field/Select_Field.php',
        'Carbon_Fields\\Field\\Separator_Field' => __DIR__ . '/..' . '/htmlburger/carbon-fields/core/Field/Separator_Field.php',
        'Carbon_Fields\\Field\\Set_Field' => __DIR__ . '/..' . '/htmlburger/carbon-fields/core/Field/Set_Field.php',
        'Carbon_Fields\\Field\\Sidebar_Field' => __DIR__ . '/..' . '/htmlburger/carbon-fields/core/Field/Sidebar_Field.php',
        'Carbon_Fields\\Field\\Text_Field' => __DIR__ . '/..' . '/htmlburger/carbon-fields/core/Field/Text_Field.php',
        'Carbon_Fields\\Field\\Textarea_Field' => __DIR__ . '/..' . '/htmlburger/carbon-fields/core/Field/Textarea_Field.php',
        'Carbon_Fields\\Field\\Time_Field' => __DIR__ . '/..' . '/htmlburger/carbon-fields/core/Field/Time_Field.php',
        'Carbon_Fields\\Helper\\Color' => __DIR__ . '/..' . '/htmlburger/carbon-fields/core/Helper/Color.php',
        'Carbon_Fields\\Helper\\Delimiter' => __DIR__ . '/..' . '/htmlburger/carbon-fields/core/Helper/Delimiter.php',
        'Carbon_Fields\\Helper\\Helper' => __DIR__ . '/..' . '/htmlburger/carbon-fields/core/Helper/Helper.php',
        'Carbon_Fields\\Libraries\\Sidebar_Manager\\Sidebar_Manager' => __DIR__ . '/..' . '/htmlburger/carbon-fields/core/Libraries/Sidebar_Manager/Sidebar_Manager.php',
        'Carbon_Fields\\Loader\\Loader' => __DIR__ . '/..' . '/htmlburger/carbon-fields/core/Loader/Loader.php',
        'Carbon_Fields\\Pimple\\Container' => __DIR__ . '/..' . '/htmlburger/carbon-fields/core/Pimple/Container.php',
        'Carbon_Fields\\Pimple\\Exception\\ExpectedInvokableException' => __DIR__ . '/..' . '/htmlburger/carbon-fields/core/Pimple/Exception/ExpectedInvokableException.php',
        'Carbon_Fields\\Pimple\\Exception\\FrozenServiceException' => __DIR__ . '/..' . '/htmlburger/carbon-fields/core/Pimple/Exception/FrozenServiceException.php',
        'Carbon_Fields\\Pimple\\Exception\\InvalidServiceIdentifierException' => __DIR__ . '/..' . '/htmlburger/carbon-fields/core/Pimple/Exception/InvalidServiceIdentifierException.php',
        'Carbon_Fields\\Pimple\\Exception\\UnknownIdentifierException' => __DIR__ . '/..' . '/htmlburger/carbon-fields/core/Pimple/Exception/UnknownIdentifierException.php',
        'Carbon_Fields\\Pimple\\ServiceIterator' => __DIR__ . '/..' . '/htmlburger/carbon-fields/core/Pimple/ServiceIterator.php',
        'Carbon_Fields\\Pimple\\ServiceProviderInterface' => __DIR__ . '/..' . '/htmlburger/carbon-fields/core/Pimple/ServiceProviderInterface.php',
        'Carbon_Fields\\Provider\\Container_Condition_Provider' => __DIR__ . '/..' . '/htmlburger/carbon-fields/core/Provider/Container_Condition_Provider.php',
        'Carbon_Fields\\REST_API\\Decorator' => __DIR__ . '/..' . '/htmlburger/carbon-fields/core/REST_API/Decorator.php',
        'Carbon_Fields\\REST_API\\Router' => __DIR__ . '/..' . '/htmlburger/carbon-fields/core/REST_API/Router.php',
        'Carbon_Fields\\Service\\Legacy_Storage_Service_v_1_5' => __DIR__ . '/..' . '/htmlburger/carbon-fields/core/Service/Legacy_Storage_Service_v_1_5.php',
        'Carbon_Fields\\Service\\Meta_Query_Service' => __DIR__ . '/..' . '/htmlburger/carbon-fields/core/Service/Meta_Query_Service.php',
        'Carbon_Fields\\Service\\REST_API_Service' => __DIR__ . '/..' . '/htmlburger/carbon-fields/core/Service/REST_API_Service.php',
        'Carbon_Fields\\Service\\Revisions_Service' => __DIR__ . '/..' . '/htmlburger/carbon-fields/core/Service/Revisions_Service.php',
        'Carbon_Fields\\Service\\Service' => __DIR__ . '/..' . '/htmlburger/carbon-fields/core/Service/Service.php',
        'Carbon_Fields\\Toolset\\Key_Toolset' => __DIR__ . '/..' . '/htmlburger/carbon-fields/core/Toolset/Key_Toolset.php',
        'Carbon_Fields\\Toolset\\WP_Toolset' => __DIR__ . '/..' . '/htmlburger/carbon-fields/core/Toolset/WP_Toolset.php',
        'Carbon_Fields\\Value_Set\\Value_Set' => __DIR__ . '/..' . '/htmlburger/carbon-fields/core/Value_Set/Value_Set.php',
        'Carbon_Fields\\Walker\\Nav_Menu_Item_Edit_Walker' => __DIR__ . '/..' . '/htmlburger/carbon-fields/core/Walker/Nav_Menu_Item_Edit_Walker.php',
        'Carbon_Fields\\Widget' => __DIR__ . '/..' . '/htmlburger/carbon-fields/core/Widget.php',
        'Carbon_Fields\\Widget\\Widget' => __DIR__ . '/..' . '/htmlburger/carbon-fields/core/Widget/Widget.php',
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
        'JustB2b\\Controllers\\BaseController' => __DIR__ . '/../..' . '/includes/Controllers/BaseController.php',
        'JustB2b\\Controllers\\LogicBlocksController' => __DIR__ . '/../..' . '/includes/Controllers/LogicBlocksController.php',
        'JustB2b\\Controllers\\RolesController' => __DIR__ . '/../..' . '/includes/Controllers/RolesController.php',
        'JustB2b\\Controllers\\RulesController' => __DIR__ . '/../..' . '/includes/Controllers/RulesController.php',
        'JustB2b\\Models\\BaseModel' => __DIR__ . '/../..' . '/includes/Models/BaseModel.php',
        'JustB2b\\Models\\LogicBlocksModel' => __DIR__ . '/../..' . '/includes/Models/LogicBlocksModel.php',
        'JustB2b\\Models\\RolesModel' => __DIR__ . '/../..' . '/includes/Models/RolesModel.php',
        'JustB2b\\Models\\RulesModel' => __DIR__ . '/../..' . '/includes/Models/RulesModel.php',
        'JustB2b\\Traits\\SingletonTrait' => __DIR__ . '/../..' . '/includes/Traits/SingletonTrait.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit8392046e6f3fcbecefee487ba1a61925::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit8392046e6f3fcbecefee487ba1a61925::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit8392046e6f3fcbecefee487ba1a61925::$classMap;

        }, null, ClassLoader::class);
    }
}
