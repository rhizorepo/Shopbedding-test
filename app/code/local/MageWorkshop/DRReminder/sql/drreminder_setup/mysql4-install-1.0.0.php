<?php
/**
 * MageWorkshop
 * Copyright (C) 2016 MageWorkshop <mageworkshophq@gmail.com>
 *
 * @category   MageWorkshop
 * @package    MageWorkshop_DRReminder
 * @copyright  Copyright (c) 2016 MageWorkshop Co. (http://mage-workshop.com)
 * @license    http://opensource.org/licenses/gpl-3.0.html GNU General Public License, version 3 (GPL-3.0)
 * @author     MageWorkshop <mageworkshophq@gmail.com>
 */

/** @var MageWorkshop_DRReminder_Model_Mysql4_Setup $installer */
$installer = $this;
$installer->startSetup();
$configValuesMap = array(
    'drreminder/settings/remind_email_template' =>
        'drreminder_settings_remind_email_template',
);

foreach ($configValuesMap as $configPath=>$configValue) {
    $installer->setConfigData($configPath, $configValue);
}

$reviewRemindersTable = $installer->getTable('drreminder/review_reminders');
$salesFlatOrderItemTable = $installer->getTable('sales_flat_order_item');
$connection = $installer->getConnection();

if ($connection->isTableExists($reviewRemindersTable) != true) {
    $table = $connection
        ->newTable($reviewRemindersTable)
        ->addColumn('id', Varien_Db_Ddl_Table::TYPE_BIGINT, 20, array(
            'nullable' => false,
            'primary' => true,
            'identity' => true
        ), 'Id')
        ->addColumn('customer_id', Varien_Db_Ddl_Table::TYPE_BIGINT, 20, array(
            'nullable' => false
        ), 'Customer Id')
        ->addColumn('customer_name', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
            'nullable' => false
        ), 'Customer Name')
        ->addColumn('email', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
            'nullable' => false
        ), 'Email')
        ->addColumn('order_id', Varien_Db_Ddl_Table::TYPE_BIGINT, 20, array(
            'nullable' => false
        ), 'Order ID')
        ->addColumn('increment_id', Varien_Db_Ddl_Table::TYPE_BIGINT, 20, array(
            'nullable' => false
        ), 'Increment Id')
        ->addColumn('creating_date', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
            'nullable' => false
        ), 'Creating Date')
        ->addColumn('expiration_date', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
            'nullable' => false
        ), 'Expiration Date')
        ->addColumn('sent_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
            'nullable' => true
        ), 'Sent At')
        ->addColumn('status', Varien_Db_Ddl_Table::TYPE_SMALLINT, 6, array(
            'nullable' => false,
        ), 'Status')
        ->addColumn('store_id', Varien_Db_Ddl_Table::TYPE_BIGINT, 3, array(
            'nullable' => false,
        ), 'Store ID');
    $table->setOption('type', 'MyISAM');
    $table->setOption('charset', 'utf8');

    $connection->createTable($table);

}

if ($connection->isTableExists($reviewRemindersTable) == true) {
    if (!$connection->tableColumnExists($reviewRemindersTable, 'store_id')) {
        $connection->addColumn($reviewRemindersTable, 'store_id', array(
            'type' => Varien_Db_Ddl_Table::TYPE_BIGINT,
            'length' => 3,
            'nullable' => false,
            'default' => '0',
            'comment' => 'Store ID'
        ));
    }
}
if (!$installer->getConnection()->tableColumnExists($installer->getTable('sales_flat_order_item'), 'reminder')) {
    $connection->addColumn($salesFlatOrderItemTable, 'reminder', array(
        'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length' => 1,
        'nullable' => true,
        'default' => '0',
        'comment' => 'Reminder'
    ));
}

$installer->endSetup();
