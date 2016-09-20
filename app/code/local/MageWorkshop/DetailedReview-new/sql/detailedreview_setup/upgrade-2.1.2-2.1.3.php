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
$connection = $installer->getConnection();
$reviewPurchase = $installer->getTable('detailedreview/purchase');

if ($connection->isTableExists($reviewPurchase) != true) {
    $table = $connection
        ->newTable($reviewPurchase)
        ->addColumn('item_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'identity'  => true,
            'unsigned'  => true,
            'nullable'  => false,
            'primary'   => true
        ), 'Item Id')
        ->addColumn('customer_email', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
            'nullable' => false
        ), 'Customer Email')
        ->addColumn('product_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'unsigned'  => true,
            'nullable' => false
        ), 'Product Id')
        ->addColumn('created_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
            'nullable'  => false
        ), 'Created At')
        ->addColumn('store_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
            'unsigned'  => true,
            'nullable'  => false
        ), 'Store Id')
        ->addIndex($installer->getIdxName($reviewPurchase, array('customer_email')),
            array('customer_email'))
        ->addIndex($installer->getIdxName($reviewPurchase, array('product_id')),
            array('product_id'))
        ->addIndex($installer->getIdxName($reviewPurchase, array('store_id')),
            array('store_id'))
        ->addIndex($installer->getIdxName('bundle/option_value', array('customer_email', 'product_id', 'store_id'), Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE),
            array('customer_email', 'product_id', 'store_id'), array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE))
        ->addForeignKey($installer->getFkName('detailedreview/purchase', 'product_id', 'catalog/product', 'entity_id'),
            'product_id', $installer->getTable('catalog/product'), 'entity_id',
            Varien_Db_Ddl_Table::ACTION_CASCADE,Varien_Db_Ddl_Table::ACTION_CASCADE)
        ->addForeignKey($installer->getFkName('detailedreview/purchase', 'store_id', 'core/store', 'store_id'),
            'store_id', $installer->getTable('core/store'), 'store_id',
            Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
        ->setComment('Purchase Table');

    $connection->createTable($table);
}

$installer->endSetup();
