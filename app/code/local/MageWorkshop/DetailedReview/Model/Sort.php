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
class MageWorkshop_DetailedReview_Model_Sort
{
    /**
     * @return Varien_Db_Adapter_Interface
     */
    public function getConnection()
    {
        return Mage::getSingleton('core/resource')->getConnection('default_setup');
    }

    /**
     * @return $this
     */
    public function refreshAllIndices()
    {
        if (Mage::getStoreConfig('detailedreview/settings/deny_change_polarity_group')) {
            $this->refreshReviewIndex();
            $this->refreshOrderIndex();
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function refreshReviewIndex()
    {
        $resource = Mage::getResourceSingleton('catalog/product');
        $productCollection = Mage::getResourceModel('detailedreview/review_reports_product_collection')->joinReview();

        $productCollection->setPageSize(100);
        $pages = $productCollection->getLastPageNumber();
        $currentPage = 1;
        do {
            $productCollection->setCurPage($currentPage);
            $productCollection->load();
            /** @var Mage_Catalog_Model_Product $product */
            foreach ($productCollection as $product) {
                $product->setData('popularity_by_reviews', ((int) $product->getReviewCnt()));
                $product->setData('popularity_by_rating', ((int) $product->getAvgRatingApproved()));
                Mage::dispatchEvent('detailedreview_sort_refreshreviewindex', array(
                    'resource' => $resource,
                    'product' => $product
                ));
                $resource->saveAttribute($product, 'popularity_by_reviews');
                $resource->saveAttribute($product, 'popularity_by_rating');
            }
            $currentPage++;
            //clear collection and free memory
            $productCollection->clear();
        } while ($currentPage <= $pages);

        $this->_updateFlatProductTable('popularity_by_reviews');
        $this->_updateFlatProductTable('popularity_by_rating');
        return $this;
    }

    /**
     * @param string $attributeCode
     * @return $this
     */
    protected function _updateFlatProductTable($attributeCode)
    {
        $indexer = Mage::getResourceModel('catalog/product_flat_indexer');
        $attribute = $indexer->getAttribute($attributeCode);
        /** @var Mage_Core_Model_Store $store */
        foreach (Mage::app()->getStores() as $store) {
            $indexer->updateAttribute($attribute, $store->getId());
        }
        
        return $this;
    }

    /**
     * @return $this
     */
    public function refreshOrderIndex()
    {
        $resource = Mage::getResourceSingleton('catalog/product');
        /* @var $soldCollection Mage_Reports_Model_Resource_Product_Collection */
        $soldCollection= Mage::getResourceModel('reports/product_collection')
            ->addOrderedQty()
            ->addFieldToFilter('sku', array('notnull' => true))
            ->setOrder('ordered_qty', 'desc');

        $soldCollection->setPageSize(100);
        $pages = $soldCollection->getLastPageNumber();
        $currentPage = 1;
        do {
            $soldCollection->setCurPage($currentPage);
            $soldCollection->load();

            /** @var Mage_Catalog_Model_Product $product */
            foreach($soldCollection as $product) {
                Mage::dispatchEvent('detailedreview_sort_refreshorderindex', array(
                    'resource' => $resource,
                    'product' => $product
                ));
                $product->setData('popularity_by_sells', (int) $product->getOrderedQty());
                $resource->saveAttribute($product, 'popularity_by_sells');
            }
            $currentPage++;
            //clear collection and free memory
            $soldCollection->clear();
        } while ($currentPage <= $pages);

        $this->_updateFlatProductTable('popularity_by_sells');
        return $this;
    }

    /**
     * @param Mage_Reports_Model_Mysql4_Product_Sold_Collection $collection
     * @return $this
     */
    protected function addOrdersCountToProductCollection($collection)
    {
        $from = $this->_getFromDate();
        $to = $this->_getToday();

        $orderItemTableName = $collection->getTable('sales/order_item');
        $productFieldName   = 'e.entity_id';

        $collection->getSelect()
            ->joinLeft(
                array('order_items' => $orderItemTableName),
                "order_items.product_id = $productFieldName",
                array()
            )
            ->columns(array('orders' => 'COUNT(order_items2.item_id)'))
            ->group($productFieldName);

        $dateFilter = array('order_items2.item_id = order_items.item_id');
        if ($from && $to) {
            $dateFilter[] = sprintf('(order_items2.created_at BETWEEN "%s" AND "%s")', $from, $to);
        }

        $collection->getSelect()
            ->joinLeft(
                array('order_items2' => $orderItemTableName),
                implode(' AND ', $dateFilter),
                array()
            );
        return $this;
    }


    /**
     * Retrieve start time for report
     * 
     * @return string
     */
    protected function _getFromDate()
    {
        $date = new Zend_Date;
        $date->subDay(10);
        return $date->getIso();
    }
    
    /**
     * Retrieve now
     * 
     * @return string
     */
    protected function _getToday()
    {
        $date = new Zend_Date;
        return $date->getIso();
    } 
}

