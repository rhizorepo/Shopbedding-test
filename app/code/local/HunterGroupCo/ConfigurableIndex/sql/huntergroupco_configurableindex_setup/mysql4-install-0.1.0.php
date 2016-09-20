<?php
/**
 * Created by JetBrains PhpStorm.
 * User: arybitskiy
 * Date: 11/27/13
 * Time: 1:21 PM
 * To change this template use File | Settings | File Templates.
 */ 
/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();


$installer->run("
	DROP TABLE IF EXISTS {$this->getTable('huntergroupco_configurableindex/saleable')};

	CREATE TABLE {$this->getTable('huntergroupco_configurableindex/saleable')} (
		`product_id` int(11) NOT NULL,
		`is_saleable` tinyint(1) NULL,
		`store_id` int NOT NULL,
		`colors` TEXT,

		KEY `is_saleable` (`is_saleable`)
	) DEFAULT CHARSET utf8 ENGINE = InnoDB;

	ALTER TABLE  {$this->getTable('huntergroupco_configurableindex/saleable')} ADD PRIMARY KEY (
        `product_id` ,
        `store_id`
    );
	");

$installer->endSetup();