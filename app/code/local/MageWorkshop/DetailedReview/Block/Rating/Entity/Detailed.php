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
 * Class MageWorkshop_DetailedReview_Block_Rating_Entity_Detailed
 *
 * @method MageWorkshop_DetailedReview_Block_Rating_Entity_Detailed setSummary(float $float)
 * @method MageWorkshop_DetailedReview_Block_Rating_Entity_Detailed setCountReviewsWithRating(float)
 */
class MageWorkshop_DetailedReview_Block_Rating_Entity_Detailed extends Mage_Core_Block_Template
{
    protected $_reviewCollections = array();
    protected $_ratingCollection;
    protected $_qtyMarks = array();
    protected $_availableSorts = array();

    /**
     * @inherit
     */
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('detailedreview/rating/detailed.phtml');
    }

    /**
     * @inherit
     */
    protected function _toHtml()
    {
        $entityId = Mage::app()->getRequest()->getParam('id');
        if (intval($entityId) <= 0) {
            return '';
        }

        $reviewsCount = Mage::getModel('review/review')
            ->getTotalReviews($entityId, true, Mage::app()->getStore()->getId());
        if ($reviewsCount == 0) {
            $this->setTemplate('detailedreview/rating/empty.phtml');
            return parent::_toHtml();
        }

        $ratingCollection = Mage::getModel('rating/rating')
            ->getResourceCollection();
        $ratingCollection->addEntityFilter('product')
            ->setPositionOrder()
            ->setStoreFilter(Mage::app()->getStore()->getId())
            ->addRatingPerStoreName(Mage::app()->getStore()->getId())
            ->load();
        Mage::dispatchEvent('detailedreview_rating_entity_detailed_ratingcollection', array(
            'collection' => $ratingCollection
        ));
        if ($entityId) {
            $ratingCollection->addEntitySummaryToItem($entityId, Mage::app()->getStore()->getId());
        }
        $this->calculateSummary();
        $this->_ratingCollection = $ratingCollection;
        $this->assign('collection', $ratingCollection);
        Mage::helper('detailedreview')->applyTheme($this);
        return parent::_toHtml();
    }

    /**
     * @return $this
     */
    public function calculateSummary()
    {
        $summary = $sum = 0;
        foreach ($this->getQtyMarks() as $key => $value) {
            if (!$key) continue;
            $summary += $key * $value * 20;
            $sum += $value;
        }
        if($sum) {
            $this->setSummary(round($summary / $sum))
                 ->setCountReviewsWithRating($sum);
        }
        return $this;
    }

    /**
     * @param int $range
     * @return mixed
     */
    public function getQtyMarks($range = 0)
    {
        if (!isset($this->_qtyMarks[$range])) {
            $reviewsIds = array();
            /** @var MAge_Review_Model_Review $review */
            foreach ($this->getReviewCollection($range) as $review) {
                $reviewsIds[] = $review->getId();
            }
            $this->_qtyMarks[$range] = Mage::getModel('detailedreview/rating_option_vote')->getQtyMarks($reviewsIds);
        }
        return $this->_qtyMarks[$range];
    }

    /**
     * @param int $range
     * @return mixed
     */
    public function getQtyByRange($range = 0) {
        /** @var MageWorkshop_DetailedReview_Model_Mysql4_Review_Collection $collection */
        $collection = $this->getReviewCollection($range);
        return $collection->count();
    }

    /**
     * @return float
     */
    public function getAverageSizing()
    {
        /** @var MageWorkshop_DetailedReview_Model_Mysql4_Review_Collection $collection */
        $collection = $this->getReviewCollection();
        return $collection->getAverageSizing();
    }

    /**
     * @param int $range
     * @return mixed
     */
    public function getReviewCollection($range = 0)
    {
        $params = Mage::app()->getRequest()->getParams();
        $range = ($range != 0) ? $range : ((isset($params['st'])) ? $params['st'] : 0);
        if (!isset($this->_reviewCollections[$range])) {
            $reviewCollection = Mage::getSingleton('detailedreview/review')->getReviewsCollection(true, $range);
            $this->_reviewCollections[$range] = $reviewCollection;
        }
        return $this->_reviewCollections[$range];
    }

    /**
     * @return mixed
     */
    public function getCurrentProduct()
    {
        return Mage::registry('current_product');
    }

    /**
     * @param bool $ratingsEnabled
     * @return array
     */
    public function getAvailableSorts($ratingsEnabled)
    {
        $options = Mage::getSingleton('detailedreview/review_sorting')->getAvailableOptions();
        if (!$ratingsEnabled){
            unset($options['rate_desc']);
            unset($options['rate_asc']);
        }
        return $options;
    }

    /**
     * @return string
     */
    public function getCurrentSorting()
    {
        return Mage::getSingleton('detailedreview/review_sorting')->getCurrentSorting();
    }

    /**
     * @return array
     */
    public function getAvailableFilterAttributes()
    {
        $helper = Mage::helper('detailedreview');
        $availableFilterAttributes = array(
            'verified_buyers' => $helper->__('Verified Buyers')
        );

        if ($helper->checkFieldAvailable('image', 'form')) {
            $availableFilterAttributes['images'] = $helper->__('Reviews with Images');
        }
        if ($helper->checkFieldAvailable('video', 'form')) {
            $availableFilterAttributes['video'] = $helper->__('Reviews with Video');
        }
        if ($helper->checkFieldAvailable('response', 'info')) {
            $availableFilterAttributes['admin_response'] = $helper->__('Administration Response');
        }
        $availableFilterAttributes['highest_contributors'] = $helper->__('Highest Contributors');

        return $availableFilterAttributes;
    }

    /**
     * @return array
     */
    public function getAvailableDateRanges()
    {
        $helper = Mage::helper('detailedreview');
        return array(
            1 => $helper->__('My Reviews'),
            2 => $helper->__('Last Week'),
            3 => $helper->__('Last 4 Weeks'),
            4 => $helper->__('Last 6 Months'),
            999 => $helper->__('All Reviews')
        );
    }
}
