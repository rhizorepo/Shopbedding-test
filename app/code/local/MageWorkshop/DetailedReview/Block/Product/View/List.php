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
class MageWorkshop_DetailedReview_Block_Product_View_List extends Mage_Review_Block_Product_View_List
{
    const XML_PATH_ALLOW_VIDEO_PREVIEW = 'detailedreview/show_review_info_settings/allow_video_preview';

    /**
     * @return Mage_Review_Model_Resource_Review_Collection|MageWorkshop_DetailedReview_Model_Mysql4_Review_Collection
     */
    public function getReviewsCollection()
    {
        if (!Mage::getStoreConfig('detailedreview/settings/enable')) {
            return parent::getReviewsCollection();
        }
        if (is_null($this->_reviewsCollection)) {
            $this->_reviewsCollection = Mage::getSingleton('detailedreview/review')->getReviewsCollection();
        }
        Mage::dispatchEvent('detailedreview_catalog_block_product_view_list_reviewscollection', array(
            'collection' => $this->_reviewsCollection
        ));
        return $this->_reviewsCollection;
    }

    /**
     * @inherit
     */
    protected function _beforeToHtml()
    {
        if (!Mage::getStoreConfig('detailedreview/settings/enable')) {
            return parent::_beforeToHtml();
        }
        /** @var MageWorkshop_DetailedReview_Model_Mysql4_Review_Collection $reviewCollection */
        $reviewCollection = $this->getReviewsCollection();
        $reviewCollection->addHelpfulInfo();
        Mage::helper('detailedreview')->applyTheme($this);
        return parent::_beforeToHtml();
    }

    public function getReviewsCountWithoutFilters()
    {
        return Mage::getSingleton('detailedreview/review')->getReviewsCountWithoutFilters();
    }
}
