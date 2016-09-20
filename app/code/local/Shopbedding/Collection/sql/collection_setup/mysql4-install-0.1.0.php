<?php
// $installer = $this;
// $installer->startSetup();

// $installer = $this;

// $installer->startSetup();

// $entityTypeId = $installer->getEntityTypeId('catalog_category');

// $installer->addAttribute($entityTypeId, 'product_sku', array(
//     'type'          => 'int',
//     'input'         => 'text',
//     'label'         => 'Product Sku',
//     'required'      => 0,
//     'group'         => 'General',
//     'global'        => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
//     'visible'       => 1
// ));

// $installer->endSetup();
///*
$installer = $this;

$installer->startSetup();

$installer->run("

		ALTER TABLE `catalog_product_entity_media_gallery_value`
		ADD COLUMN `is_category` tinyint(1) unsigned NOT NULL DEFAULT '0';
		");

$installer->run("

		ALTER TABLE `catalog_product_entity_media_gallery_value`
		ADD COLUMN `swatchimage` varchar(255);
		");

$installer->run("

		ALTER TABLE `catalog_product_entity_media_gallery_value`
		ADD COLUMN `swatchimage_label` varchar(255);
");

$installer->endSetup();
