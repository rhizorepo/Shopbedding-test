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
if (!$connection->tableColumnExists($installer->getTable('review/review_detail'), 'customer_email')) {
    $connection->addColumn($installer->getTable('review/review_detail'), 'customer_email', array(
            'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
            'length' => 127,
            'nullable' => true,
            'default' => null,
            'comment' => 'Customer Email'
        )
    );
}

$installer->endSetup();
