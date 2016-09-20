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
$reviewHelpfulTable = $installer->getTable('detailedreview/review_helpful');

if ($connection->isTableExists($reviewHelpfulTable) == true) {
    $connection->addIndex(
        $reviewHelpfulTable,
        $installer->getIdxName('detailedreview/review_helpful', array('review_id')),
        array('review_id')
    );

    $connection->addForeignKey(
        $installer->getFkName('detailedreview/review_helpful', 'review_id', 'review/review', 'review_id'),
        $installer->getTable('detailedreview/review_helpful'),
        'review_id',
        $installer->getTable('review/review'),
        'review_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE,
        Varien_Db_Ddl_Table::ACTION_CASCADE
    );
}

/* make 'review_fields_available' attr not required */
$installer->updateAttribute('catalog_category',  'review_fields_available', 'is_required', '0');

$installer->endSetup();
