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

class MageWorkshop_DRReminder_Block_Review_Reminder_List extends Mage_Core_Block_Template
{
    /**
     * @return array|Mage_Catalog_Model_Resource_Product_Collection
     */
    public function getNewProductsToReview()
    {
        $result = array();
        $customerIdentifier = Mage::helper('drreminder')->getCustomerIdentifier();
        if ($customerIdentifier) {
            /** @var MageWorkshop_DRReminder_Model_Review_Reminder_List $reminder */
            $reminder = Mage::getModel('drreminder/review_reminder_list');
            $result = $reminder->getNewProductsToReview($customerIdentifier);
        }
        return $result;
    }

    /**
     * @return Mage_Catalog_Model_Resource_Product_Collection|null
     */
    public function getAllProductsToReview()
    {
        $result = array();
        $customerIdentifier = Mage::helper('drreminder')->getCustomerIdentifier();
        if ($customerIdentifier) {
            /** @var MageWorkshop_DRReminder_Model_Review_Reminder_List $reminder */
            $reminder = Mage::getModel('drreminder/review_reminder_list');
            $result = $reminder->getAllProductsToReview($customerIdentifier, $this->getNewProductsToReview());
        }
        return $result;
    }

    /**
     * @param $ids
     * @return Mage_Catalog_Model_Resource_Product_Collection
     */
    public function getProductCollectionByIds($ids)
    {
        return Mage::getModel('catalog/product')->getCollection()
            ->addAttributeToSelect('*')
            ->addFieldToFilter('entity_id', array('in' => $ids));
    }
}
