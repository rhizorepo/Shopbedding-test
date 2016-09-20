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

class MageWorkshop_DetailedReview_Block_Customer_Info extends Mage_Core_Block_Template
{
    /**
     * Product reviews collectionWithoutLimit
     *
     * @var Mage_Review_Model_Resource_Review_Product_Collection
     */
    protected $_collection;

    public function __construct()
    {
        parent::__construct();
        $this->_collection = Mage::getModel('review/review')->getProductCollection();
        $this->_collection
            ->addStoreFilter(Mage::app()->getStore()->getId())
            ->addCustomerFilter(Mage::getSingleton('customer/session')->getCustomerId())
            ->setDateOrder()
            ->setPageSize(false);
    }

    /**
     * Get collection
     *
     * @return Mage_Review_Model_Resource_Review_Product_Collection
     */
    public function getCollection()
    {
        return $this->_collection;
    }

    /**
     * @return float
     */
    public function getAverageRating()
    {
        $sum = 0;
        /** @var Mage_Catalog_Model_Product $item */
        foreach ($this->getCollection() as $item) {
            $sum += $item->getSum() / (20 * $item->getCount());
        }
        return round($sum / $this->getCollection()->count());
    }

    /**
     * @return int
     */
    public function getHelpfulVotes()
    {
        $sum = 0;
        /** @var Mage_Catalog_Model_Product $item */
        foreach ($this->getCollection() as $item) {
            $sum += $item->getCountHelpful();
        }
        return $sum;
    }

    /**
     * Format date in short format
     *
     * @param $date
     * @return string
     */
    public function dateFormat($date)
    {
        return $this->formatDate($date, Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);
    }

    /**
     * @inherit
     */
    protected function _toHtml()
    {
        /** @var MageWorkshop_DetailedReview_Helper_Data $helper */
        $this->getCollection()
            ->load()
            ->addReviewSummary();
        $helper = $this->helper('detailedreview');
        $helper->applyTheme($this);
        return parent::_toHtml();
    }
}
