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
    $installer = new Mage_Customer_Model_Entity_Setup('core_setup');
    $installer->addAttribute('customer', 'is_banned_write_review', array(
        'type' => 'int',
        'input' => 'select',
        'label' => 'Is Banned from Write a Review',
        'global' => 1,
        'visible' => 1,
        'required' => 0,
        'user_defined' => 1,
        'default' => '0',
        'visible_on_front' => 0,
    ));
    $installer->updateAttribute('customer', 'is_banned_write_review', 'source_model', 'eav/entity_attribute_source_boolean');
    $connection->commit();
} catch (Exception $e) {
    $connection->rollback();
    throw $e;
}
$installer->endSetup();
