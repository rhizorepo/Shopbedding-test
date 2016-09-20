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

/**
 * Class MageWorkshop_DetailedReview_Model_Review_Products_List
 */
class MageWorkshop_DetailedReview_Model_Review_Products_List extends Mage_Core_Model_Abstract
{

    public  function _construct()
    {
        $this->_init('detailedreview/review_products_list');
    }

    /**
     * @param $orderId
     * @return Mage_Catalog_Model_Resource_Product_Collection
     */
    public function getCurrentOrderProducts($orderId)
    {
        $productCollection = Mage::helper('detailedreview')->getProductsByOrders($orderId);
        $productCollection->addAttributeToSelect('*');
        return $productCollection;
    }
    /**
     * @param $customerIdentifier
     * @param Mage_Catalog_Model_Resource_Product_Collection $originalOrderProductCollection
     * @return Mage_Catalog_Model_Resource_Product_Collection|null
     */
    public function getAllProductsForReview($customerIdentifier, Mage_catalog_Model_Resource_Product_Collection $originalOrderProductCollection)
    {
        $orderCollection = Mage::getResourceModel('sales/order_collection');
        $orderCollection->addFieldToFilter($customerIdentifier['type'], $customerIdentifier['value'])
            ->addFieldToFilter('entity_id', array('neq' => $customerIdentifier['order_id']));

        if (count($orderCollection->getAllIds()) == 0) {
            return false;
        }
        $productCollection = Mage::helper('detailedreview')->getProductsByOrders($orderCollection->getAllIds());

        $productCollection->addFieldToFilter('entity_id', array('nin' => $originalOrderProductCollection->getAllIds()));
        return $productCollection;
    }
}
