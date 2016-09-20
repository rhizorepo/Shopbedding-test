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
$connection->beginTransaction();

try {
    $installer->run("
      UPDATE {$installer->getTable('review_detail')} SET image = REPLACE(image, 'media/', '') WHERE image LIKE 'media/%';
    ");
    $installer->run("
      UPDATE {$installer->getTable('review_detail')} SET image = REPLACE(image, 'media\\\\', '') WHERE image LIKE 'media\\\\\\\\%';
    ");

    $installer->updateAttribute('catalog_category',  'use_parent_review_settings', 'frontend_input_renderer', 'detailedreview/adminhtml_catalog_category_helper_fields_useParent');
    $installer->updateAttribute('catalog_category',  'review_fields_available', 'is_required', '1');

    $connection->commit();
} catch (Exception $e) {
    $connection->rollback();
    throw $e;
}

$installer->endSetup();
