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

class MageWorkshop_DetailedReview_Block_Review_Products_List extends Mage_Core_Block_Template
{
    /**
     * @return Mage_Catalog_Model_Resource_Product_Collection
     */
    public function getCurrentOrderProducts()
    {
        $orderId = (string) Mage::app()->getRequest()->getParam('order');
        $productCollection = Mage::getModel('detailedreview/review_products_list')->getCurrentOrderProducts($orderId);
        return $productCollection;
    }

    /**
     * @return Mage_Catalog_Model_Resource_Product_Collection|null
     */
    public function getAllProductsForReview()
    {
        $customerIdentifier = Mage::helper('detailedreview')->getCustomerData();
        $customerIdentifier['order_id'] = (string) Mage::app()->getRequest()->getParam('order');
        $productCollection = Mage::getModel('detailedreview/review_products_list')->getAllProductsForReview($customerIdentifier, $this->getCurrentOrderProducts());
        return $productCollection;
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
