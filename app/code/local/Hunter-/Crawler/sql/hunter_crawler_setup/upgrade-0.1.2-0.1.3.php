<?php
/**
 * Add primary key ot page_key field
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

$tableName = $installer->getTable('hunter_crawler/crawler_queue');
$installer->getConnection()
    ->addColumn($tableName,
        'is_locked',
        array(
            'type' => Varien_Db_Ddl_Table::TYPE_INTEGER,
            'length' => 1,
            'nullable' => false,
            'default' => 0,
            'comment' => 'To lock row for processing'
        )
    );

$installer->endSetup();