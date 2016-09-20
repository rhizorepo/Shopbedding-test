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

    $connection->addColumn($installer->getTable('review/review_detail'), 'recommend_to', array(
        'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length' => 127,
        'nullable' => true,
        'default' => null,
        'comment' => 'Recommend to'
        )
    );

    $installer->updateAttribute('catalog_category', 'review_fields_available', array(
        'default_value'              => NULL,
        'frontend_input_renderer'    => 'detailedreview/adminhtml_catalog_category_helper_fields_available',
    ));

    $sql = "SELECT * FROM {$installer->getTable('eav_attribute')} WHERE attribute_code = 'use_parent_review_settings'";
    $data = Mage::getSingleton('core/resource')->getConnection('core_read')->fetchAll($sql);
    if (!empty($data)) {
        $categories = Mage::getModel('catalog/category')->getCollection()
            ->addAttributeToSelect('*');
        $categories->load();

        foreach ($categories as $category) {
            if ($category->getData('use_parent_review_settings') == 1) {
                $category->setData('review_fields_available', NULL);
                $category->getResource()->saveAttribute($category, 'review_fields_available');
            }
        }
    }

    if (!empty($data)) {
        $installer->removeAttribute('catalog_category', 'use_parent_review_settings');
    }

$installer->endSetup();
