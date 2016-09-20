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
 * Class MageWorkshop_DetailedReview_Model_Review_Helpful
 *
 * @method bool getIsHelpful()
 * @method MageWorkshop_DetailedReview_Model_Review_Helpful setIsHelpful(bool $customerId)
 * @method int getCustomerId()
 * @method MageWorkshop_DetailedReview_Model_Review_Helpful setCustomerId(int $customerId)
 * @method int getReviewId()
 * @method string getRemoteAddr()
 * @method MageWorkshop_DetailedReview_Model_Review_Helpful setRemoteAddr(string $customerId)
 */
class MageWorkshop_DetailedReview_Model_Review_Helpful extends Mage_Core_Model_Abstract
{
    public function __construct()
    {
        $this->_init('detailedreview/review_helpful');
    }

    /**
     * @return array|bool
     */
    public function validate()
    {
        $errors = array();

        $helper = Mage::helper('detailedreview');

        if (!$this->getCustomerId() && !Mage::getStoreConfig('detailedreview/settings_customer/allow_guest_vote')) {
            $errors[] = $helper->__('Guest can\'t vote');
        }

        if (!Zend_Validate::is($this->getReviewId(), 'NotEmpty')) {
            $errors[] = $helper->__('Review Id can\'t be empty');
        }

        if (empty($errors)) {
            if (!$this->getCustomerId()) {
                if ($this->getCollection()
                    ->addReviewFilter($this->getReviewId())
                    ->addRemoteAddressFilter($this->getRemoteAddr())
                    ->getSize()
                ) {
                    $errors[] = $helper->__('Guest can\'t vote twice');
                }
            } elseif ($this->getCollection()
                    ->addReviewFilter($this->getReviewId())
                    ->addCustomerFilter($this->getCustomerId())
                    ->getSize()
            ) {
                $errors[] = $helper->__('Customer can\'t vote twice');
            } else {
                /** @var MageWorkshop_DetailedReview_Model_Review $review */
                $review = Mage::getModel('review/review')->load($this->getReviewId());
                if ($review->getCustomerId() == $this->getCustomerId()) {
                    $errors[] = $helper->__('Customer can\'t vote for his own review');
                }
            }
        }
        $this->setIsHelpful((bool) $this->getIsHelpful());
        return $errors;
    }

    /**
     * @param int $reviewId
     * @return int
     */
    public function getIsCustomerVoted($reviewId)
    {
        $customerId = Mage::getSingleton('customer/session')->getCustomerId();
        if ($customerId == 0) { return 0; }
        return $this->getCollection()->addReviewFilter($reviewId)->addCustomerFilter($customerId)->count();
    }

    /**
     * @param int $reviewId
     * @return int
     */
    public function getQtyHelpfulVotesForReview($reviewId)
    {
        return $this->getCollection()->addReviewFilter($reviewId)->addHelpfulFilter()->count();
    }

    /**
     * @param int $reviewId
     * @return int
     */
    public function getQtyUnhelpfulVotesForReview($reviewId)
    {
        return $this->getQtyVotesForReview($reviewId) - $this->getQtyHelpfulVotesForReview($reviewId);
    }

    /**
     * @param int $reviewId
     * @return int
     */
    public function getQtyVotesForReview($reviewId)
    {
        return $this->getCollection()->addReviewFilter($reviewId)->count();
    }
}
