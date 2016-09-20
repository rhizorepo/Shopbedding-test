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
$authorIpsTable = $installer->getTable('detailedreview/author_ips');
$connection = $installer->getConnection();

    if ($connection->isTableExists($authorIpsTable) != true) {
        $table = $connection
            ->newTable($authorIpsTable)
            ->addColumn('id', Varien_Db_Ddl_Table::TYPE_BIGINT, 20, array(
                'nullable' => false,
                'primary' => true,
                'identity' => true
            ), 'Id')
            ->addColumn('expiration_time', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
                'nullable' => false
            ), 'Expiration time')
            ->addColumn('remote_addr', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
                'nullable' => true
            ), 'Remote ip address')
            ->addColumn('customer_id', Varien_Db_Ddl_Table::TYPE_BIGINT, 20, array(
                'nullable' => true
            ), 'Customer Id')
            ->addIndex($installer->getIdxName($authorIpsTable, array('remote_addr')),
                array('remote_addr'))
            ->addIndex($installer->getIdxName($authorIpsTable, array('customer_id')),
                array('customer_id'))
            ->setComment('Author Ips');

        $table->setOption('type', 'MyISAM');
        $table->setOption('charset', 'utf8');

        $connection->createTable($table);
    }

    $connection->addColumn($installer->getTable('review/review_detail'), 'remote_addr', array(
            'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
            'length' => 255,
            'comment' => 'Remote Author IP'
        )
    );

$installer->endSetup();
