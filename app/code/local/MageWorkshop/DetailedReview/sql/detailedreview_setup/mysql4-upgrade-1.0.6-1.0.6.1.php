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
$reviewHelpfulTable = $installer->getTable('detailedreview/review_helpful');

$connection = $installer->getConnection();

    if ($reviewHelpfulTable != 'review_helpful') {
        $sql = "SELECT * FROM " . $installer->getTable('review_helpful');
        $data = Mage::getSingleton('core/resource')->getConnection('core_read')->fetchAll($sql);
        if ($connection->isTableExists($installer->getTable('review_helpful') == true)) {
            $connection->dropTable('review_helpful');
        }

        if ($connection->isTableExists($installer->getTable('detailedreview/review_helpful')) != true) {
            $table = $connection
                ->newTable($reviewHelpfulTable)
                ->addColumn('id', Varien_Db_Ddl_Table::TYPE_BIGINT, 20, array(
                    'nullable' => false,
                    'primary' => true,
                    'identity' => true
                ), 'Id')
                ->addColumn('review_id', Varien_Db_Ddl_Table::TYPE_BIGINT, 20, array(
                    'nullable' => false
                ), 'Review Id')
                ->addColumn('customer_id', Varien_Db_Ddl_Table::TYPE_BIGINT, 20, array(
                    'nullable' => false
                ), 'Customer Id')
                ->addColumn('is_helpful', Varien_Db_Ddl_Table::TYPE_TINYINT, 1, array(
                    'nullable' => false
                ), 'Is helpful')
                ->setComment('Review Helpful Table');
            $table->setOption('type', 'MyISAM');
            $table->setOption('charset', 'utf8');

            $connection->createTable($table);
        }

        foreach ($data as $item) {
            $model = Mage::getModel('detailedreview/review_helpful');
            unset($item['id']);
            $model->setData($item);
            $model->save();
        }
    }

$installer->endSetup();
