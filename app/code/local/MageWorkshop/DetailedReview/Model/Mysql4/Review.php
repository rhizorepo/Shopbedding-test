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
class MageWorkshop_DetailedReview_Model_Mysql4_Review extends Mage_Review_Model_Mysql4_Review
{
    /**
     * @param Mage_Core_Model_Abstract $review
     * @return $this
     */
    protected function _beforeSave(Mage_Core_Model_Abstract $review)
    {
        /** @var MageWorkshop_DetailedReview_Model_Review $review */
         if (!$review->getId()) {
             $currentDate = Mage::getSingleton('core/date')->gmtDate();
         } else {
             $date = new Zend_Date($review->getCreatedAt(), Mage::app()->getLocale()->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM));
             $timestampWithOffset = $date->get() - Mage::getSingleton('core/date')->getGmtOffset();
             $currentDate = date('Y-m-d H:i:s', $timestampWithOffset);
         }
        $review->setCreatedAt($currentDate);
        if ($review->hasData('stores') && is_array($review->getStores())) {
            $stores = $review->getStores();
            $stores[] = 0;
            $review->setStores($stores);
        } elseif ($review->hasData('stores')) {
            $review->setStores(array($review->getStores(), 0));
        }
        return $this;
    }

    /**
     * @param Mage_Core_Model_Abstract $review
     * @return $this
     */
    protected function _afterSave(Mage_Core_Model_Abstract $review)
    {
        /** @var MageWorkshop_DetailedReview_Model_Review $review */
        if (!Mage::getStoreConfig('detailedreview/settings/enable')) {
            return parent::_afterSave($review);
        }

        $image = $review->getImage();
        $request = Mage::app()->getRequest();
        if ($review->getPros() && (is_null($request->getParam('pros')) && is_null($request->getParam('user_pros')))) {
            $review->setPros(null);
        }
        if ($review->getCons() && (is_null($request->getParam('cons')) && is_null($request->getParam('user_cons')))) {
            $review->setCons(null);
        }

        /**
         * save details
         */
        $detail = array(
            'title'         => $review->getTitle(),
            'video'         => $review->getVideo(),
            'image'         => ($image === null) ? '' : $image,
            'detail'        => $review->getDetail(),
            'good_detail'   => $review->getGoodDetail(),
            'no_good_detail'=> $review->getNoGoodDetail(),
            'pros'          => (is_array($review->getPros())) ? implode(',', $review->getPros()) : $review->getPros(),
            'cons'          => (is_array($review->getCons())) ? implode(',', $review->getCons()) : $review->getCons(),
            'recommend_to'  => $review->getRecommendTo(),
            'nickname'      => $review->getNickname(),
            'response'      => ($review->getResponse() === null) ? '' : $review->getResponse(),
            'sizing'        => $review->getSizing(),
            'body_type'     => (int) $review->getBodyType(),
            'location'      => $review->getLocation(),
            'age'           => ((int) $review->getAge()) ? ((int) $review->getAge()) : null,
            'height'        => ((float) $review->getHeight()) ? (float) $review->getHeight() : null,
            'customer_email'=> $review->getCustomerEmail() ? $review->getCustomerEmail() : null
        );


        $select = $this->_getWriteAdapter()
            ->select()
            ->from($this->_reviewDetailTable, 'detail_id')
            ->where('review_id = ?', $review->getId());

        if ($detailId = (int) $this->_getWriteAdapter()->fetchOne($select)) {
            $this->_getWriteAdapter()->update(
                $this->_reviewDetailTable,
                $detail,
                "detail_id = $detailId"
            );
        } else {
            $detail['store_id']    = $review->getStoreId();
            $detail['customer_id'] = $review->getCustomerId();
            $detail['review_id']   = $review->getId();
            $detail['remote_addr'] = Mage::helper('core/http')->getRemoteAddr();
            $this->_getWriteAdapter()->insert($this->_reviewDetailTable, $detail);
        }

        // Save stores
        $stores = $review->getStores();
        if (!empty($stores)) {
            $condition = $this->_getWriteAdapter()->quoteInto('review_id = ?', $review->getId());
            $this->_getWriteAdapter()->delete($this->_reviewStoreTable, $condition);

            $insertedStoreIds = array();
            foreach ($stores as $storeId) {
                if (in_array($storeId, $insertedStoreIds)) {
                    continue;
                }

                $insertedStoreIds[] = $storeId;
                $storeInsert = array(
                    'store_id' => $storeId,
                    'review_id'=> $review->getId()
                );
                $this->_getWriteAdapter()->insert($this->_reviewStoreTable, $storeInsert);
            }
        }

        // re-aggregate ratings, that depend on this review
        $this->_aggregateRatings(
            $this->_loadVotedRatingIds($review->getId()),
            $review->getEntityPkValue()
        );

        return $this;
    }

}
