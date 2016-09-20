<?php
/**
 * Creation of crawler result table
 *
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the MIT License
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/mit-license.php
 * 
 * @category    Hunter
 * @package     Hunter_Crawler
 * @copyright   Copyright (c) 2015
 * @license     http://opensource.org/licenses/mit-license.php MIT License
 * @author      Roman Tkachenko roman.tkachenko@huntersconsult.com
 */ 
/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$tableName = $installer->getTable('hunter_crawler/crawler_result');
if ($installer->tableExists($tableName)) {
    $this->getConnection()->dropTable($tableName);
}

$table = $installer->getConnection()
    ->newTable($tableName)
    ->addColumn('entity_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
    ), 'Entity Id')
    ->addColumn('date', Varien_Db_Ddl_Table::TYPE_DATETIME, null, array(
        'nullable'  => false,
    ), 'Date of the cache invalidation')
    ->addColumn('page_title', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
        'nullable'  => false,
    ), 'Page\'s title')
    ->addColumn('page_url', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
        'nullable'  => false,
    ), 'URL of the page')
    ->addColumn('first_request', Varien_Db_Ddl_Table::TYPE_DECIMAL, '10,6', array(), 'First request')
    ->addColumn('second_request', Varien_Db_Ddl_Table::TYPE_DECIMAL, '10,6', array(), 'Second request');
$installer->getConnection()->createTable($table);

$installer->endSetup();