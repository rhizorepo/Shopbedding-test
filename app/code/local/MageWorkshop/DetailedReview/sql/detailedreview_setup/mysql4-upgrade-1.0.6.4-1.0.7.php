<?php
/**
 * MageWorkshop
 * Copyright (C) 2016 MageWorkshop <mageworkshophq@gmail.com>
 *
 * @category   MageWorkshop
 * @package    MageWorkshop_DetailedReview
 * @copyright  Copyright (c) 2016 MageWorkshop Co. (http://mage-workshop.com)
 * @license    http://opensource.org/licenses/gpl-3.0.html GNU General Public License, version 3 (GPL-3.0)
 * @author     MageWorkshop <mageworkshophq@gmail.com>
 */

/** @var MageWorkshop_DetailedReview_Model_Mysql4_Setup $installer */
$installer = $this;
$installer->startSetup();
$reviewProsconsTable = $installer->getTable('review_proscons');
$reviewProsconsStoreTable = $installer->getTable('review_proscons_store');
$connection = $installer->getConnection();

    if ($connection->isTableExists($reviewProsconsTable) != true) {
        $table = $connection
            ->newTable($reviewProsconsTable)
            ->addColumn('entity_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, 5, array(
                'unsigned'  => true,
                'nullable' => false,
                'primary' => true,
                'identity' => true
            ), 'Entity Id')
            ->addColumn('name', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
                'nullable' => false
            ), 'Name')
            ->addColumn('status', Varien_Db_Ddl_Table::TYPE_TINYINT, 1, array(
                'default' => null
            ), 'Status')
            ->addColumn('wrote_by', Varien_Db_Ddl_Table::TYPE_TINYINT, 1, array(
                'nullable' => false,
                'default' => '0'
            ), 'Wrote By')
            ->addColumn('sort_order', Varien_Db_Ddl_Table::TYPE_SMALLINT, 5, array(
                'default' => null
            ), 'Sort Order')
            ->addColumn('entity_type', Varien_Db_Ddl_Table::TYPE_TEXT, 1, array(
                'nullable' => false
            ), 'Entity Type')
            ->setComment('Review Helpful Table');
        $connection->createTable($table);
    }

    if ($connection->isTableExists($reviewProsconsStoreTable) != true) {
        $table = $connection
            ->newTable($reviewProsconsStoreTable)
            ->addColumn('entity_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, 6, array(
                'unsigned' => true,
                'nullable' => false,
            ), 'Entity Id')
            ->addColumn('entity_type', Varien_Db_Ddl_Table::TYPE_TEXT, 1, array(
                'nullable' => false
            ), 'Entity Type')
            ->addColumn('store_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, 6, array(
                'unsigned' => true,
                'nullable' => false,
            ), 'Store ID')
            ->setComment('Review Helpful Table');
        $connection->createTable($table);
    }

    $connection->addColumn($installer->getTable('review/review_detail'), 'pros', array(
        'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length' => 255,
        'nullable' => true,
        'comment' => 'Pros'
    ));
    $connection->addColumn($installer->getTable('review/review_detail'), 'cons', array(
        'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length' => 255,
        'nullable' => true,
        'comment' => 'Cons'
    ));

    $connection->addColumn($installer->getTable('review_helpful'), 'remote_addr', array(
        'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length' => 255,
        'nullable' => true,
        'default' => null,
        'comment' => 'Cons'
    ));

    $connection->changeColumn($installer->getTable('review_helpful'), 'customer_id', 'customer_id', array(
        'type' => Varien_Db_Ddl_Table::TYPE_BIGINT,
        'nullable' => true,
        'default' => null
        )
    );

$installer->endSetup();
