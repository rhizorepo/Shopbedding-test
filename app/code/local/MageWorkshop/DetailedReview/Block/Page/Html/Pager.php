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
class MageWorkshop_DetailedReview_Block_Page_Html_Pager extends Mage_Page_Block_Html_Pager
{
    /**
     * @inherit
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_pageVarName    = 'r_p';
        $this->_limitVarName   = 'r_limit';

        $availableLimitsArray = array();
        if ($limitsFlatArray = explode(',', Mage::getStoreConfig('detailedreview/list_options/qty_available'))) {
            foreach ($limitsFlatArray as $limit) {
                $availableLimitsArray[$limit] = $limit;
            }
        }
        $this->_availableLimit = $availableLimitsArray;

        $reviewsPerPage = (int) $this->getRequest()->getParam('r_limit');
        if ($reviewsPerPage && in_array($reviewsPerPage, $availableLimitsArray)) {
            $this->setLimit($reviewsPerPage);
        } else {
            $this->setLimit(Mage::getStoreConfig('detailedreview/list_options/qty_default'));
        }
    }

    /**
     * @param $number
     * @return string
     */
    public function getPageNumber($number)
    {
        return (string) $number;
    }

    /**
     * @return string
     */
    public function getFirstPageNumber()
    {
        return $this->getPageNumber(1);
    }

    /**
     * @return string
     */
    public function getPreviousPageNumber()
    {
        return $this->getPageNumber($this->getCollection()->getCurPage() - 1);
    }

    /**
     * @return string
     */
    public function getNextPageNumber()
    {
        return $this->getPageNumber($this->getCollection()->getCurPage() + 1);
    }

    /**
     * @return string
     */
    public function getLastPageNumber()
    {
        return $this->getPageNumber($this->getCollection()->getLastPageNumber());
    }

    /**
     * @return string
     */
    public function getPreviousJumpNumber()
    {
        return $this->getPageNumber($this->getPreviousJumpPage());
    }

    /**
     * @return string
     */
    public function getNextJumpNumber()
    {
        return $this->getPageNumber($this->getNextJumpPage());
    }

    /**
     * Set collection for pagination
     *
     * @param  MageWorkshop_DetailedReview_Model_Mysql4_Review_Collection $collection
     * @return Mage_Page_Block_Html_Pager
     */
    public function setCollection($collection)
    {
        $reviewId = $this->getRequest()->getParam('r_id');

        // use $tempCollection->getItems() because $tempCollection->getAllIds() return incorrect order
        if (isset($reviewId) && $reviewId) {
            $tempCollection = clone $collection;
            $pageNum = (int) ceil((array_search($reviewId, array_keys($tempCollection->getItems())) + 1) / $this->getLimit());
        }

        $this->_collection = $collection
            ->setCurPage(isset($pageNum) ? $pageNum : $this->getCurrentPage());
        // If not int - then not limit
        if ((int) $this->getLimit()) {
            $this->_collection->setPageSize($this->getLimit());
        }

        $this->_setFrameInitialized(false);

        return $this;
    }
}
