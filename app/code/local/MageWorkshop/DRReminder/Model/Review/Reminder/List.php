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

/**
 * Class MageWorkshop_DRReminder_Model_Review_Reminder_List
 */
class MageWorkshop_DRReminder_Model_Review_Reminder_List extends Mage_Core_Model_Abstract
{

    public  function _construct()
    {
        $this->_init('drreminder/review_reminder_list');
    }

    /**
     * @param MageWorkshop_DRReminder_Model_CustomerIdentifier $customerIdentifier
     * @param Mage_Catalog_Model_Resource_Product_Collection $originalOrderProductCollection
     * @return Mage_Catalog_Model_Resource_Product_Collection|null
     */
    public function getAllProductsToReview(
        MageWorkshop_DRReminder_Model_CustomerIdentifier $customerIdentifier,
        Mage_Catalog_Model_Resource_Product_Collection $originalOrderProductCollection
    )
    {
        $orderCollection = Mage::getResourceModel('sales/order_collection');
        if ($customerIdentifier) {
        $orderCollection->addFieldToFilter($customerIdentifier->getType(), $customerIdentifier->getValue())
            ->addFieldToFilter('entity_id', array('neq' => $customerIdentifier->getOrderId()));
        } else {
            return false;
        }

        if (count($orderCollection->getAllIds()) == 0) {
            return false;
        }
        $productCollection = Mage::helper('drreminder')->getProductsByOrders($orderCollection->getAllIds(), false);
        if(count($originalOrderProductCollection->getAllIds())) {
            $productCollection->addFieldToFilter('entity_id', array('nin' => $originalOrderProductCollection->getAllIds()));
        }
        $this->_addNotReviewedFilterToCollection($productCollection, $customerIdentifier);
        return $productCollection;
    }


    /**
     * @param MageWorkshop_DRReminder_Model_CustomerIdentifier $customerIdentifier
     * @return Mage_Catalog_Model_Resource_Product_Collection
     */
    public function getNewProductsToReview(MageWorkshop_DRReminder_Model_CustomerIdentifier $customerIdentifier)
    {
        $productCollection = Mage::helper('drreminder')->getProductsByOrders($customerIdentifier->getOrderId(), false);
        $productCollection->addAttributeToSelect('*');
        $this->_addNotReviewedFilterToCollection($productCollection, $customerIdentifier);
        return $productCollection;
    }

    /**
     * @param $productCollection Mage_catalog_Model_Resource_Product_Collection
     * @param MageWorkshop_DRReminder_Model_CustomerIdentifier $customerIdentifier
     * @return Mage_Catalog_Model_Resource_Product_Collection
     */
    protected function _addNotReviewedFilterToCollection($productCollection, MageWorkshop_DRReminder_Model_CustomerIdentifier $customerIdentifier)
    {
        $code = Mage_Review_Model_Review::ENTITY_PRODUCT_CODE;
        $reviewCollection = Mage::getModel('review/review')->getCollection();
        $this->_setResourceModel('review/review');
        $reviewCollection
            ->addFieldToFilter($customerIdentifier->getType(), $customerIdentifier->getValue())
            ->getSelect()
            ->joinInner(
                array('re' => $this->getResource()->getTable('review/review_entity')),
                "main_table.entity_id = re.entity_id AND re.entity_code = '$code'",
                array()
            )
            ->reset(Zend_Db_Select::COLUMNS)
            ->columns('entity_pk_value');

        $productIds =  $reviewCollection->getColumnValues('entity_pk_value');

        if ($productIds) {
            $productCollection->addFieldToFilter('entity_id', array('nin' => $productIds));
        }

        return $productCollection;
    }
}
