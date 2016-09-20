<?php
$this->startSetup();
$this->addAttribute(Mage_Catalog_Model_Category::ENTITY, 'category_image_alt', array(
    'group'         => 'General',
    'input'         => 'text',
    'type'          => 'text',
    'label'         => 'Category Image Alt Text',
    'backend'       => '',
    'visible'       => true,
    'required'      => false,
    'visible_on_front' => false,
    'global'        => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
));
 
$this->endSetup();