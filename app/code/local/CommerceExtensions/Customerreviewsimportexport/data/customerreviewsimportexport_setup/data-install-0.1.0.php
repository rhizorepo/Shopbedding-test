<?php

$installer = $this;
$installer->startSetup();

$connection = $installer->getConnection();
$dbname = (string)Mage::getConfig()->getNode('global/resources/default_setup/connection/dbname');

$sql = "
  SELECT COUNT(*) As ColumnCount
  FROM information_schema.COLUMNS 
  WHERE TABLE_SCHEMA = '{$dbname}' 
  AND TABLE_NAME = '{$installer->getTable('dataflow/profile')}' 
  AND COLUMN_NAME = 'is_commerce_extensions'
";
$statement = $connection->query($sql);
$result = $statement->fetch(); 

if($result['ColumnCount'] <= 0){
    #$connection->addColumn($installer->getTable('dataflow/profile'), 'is_commerce_extensions'," TINYINT( 1 ) NOT NULL DEFAULT '0'");
	$installer->run("
	ALTER TABLE `{$installer->getTable('dataflow/profile')}` ADD `is_commerce_extensions` TINYINT( 1 ) NOT NULL DEFAULT '0'
	");
}

$installer->endSetup();