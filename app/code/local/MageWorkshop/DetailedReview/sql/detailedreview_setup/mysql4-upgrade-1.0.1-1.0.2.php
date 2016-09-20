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
$connection = $installer->getConnection();

    $connection->addColumn($installer->getTable('review/review_detail'), 'sizing', array(
        'type' => Varien_Db_Ddl_Table::TYPE_INTEGER,
        'length' => 2,
        'nullable' => true,
        'comment' => 'Sizing'
    ));
    $connection->addColumn($installer->getTable('review/review_detail'), 'body_type', array(
        'type' => Varien_Db_Ddl_Table::TYPE_INTEGER,
        'length' => 2,
        'nullable' => true,
        'comment' => 'Body Type'
    ));
    $connection->addColumn($installer->getTable('review/review_detail'), 'location', array(
        'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length' => 255,
        'nullable' => true,
        'comment' => 'Location'
    ));
    $connection->addColumn($installer->getTable('review/review_detail'), 'age', array(
        'type' => Varien_Db_Ddl_Table::TYPE_INTEGER,
        'length' => 3,
        'nullable' => true,
        'comment' => 'Age'
    ));
    $connection->addColumn($installer->getTable('review/review_detail'), 'height', array(
        'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length' => 20,
        'nullable' => true,
        'comment' => 'Height'
    ));

$installer->endSetup();
