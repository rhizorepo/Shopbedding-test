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
class MageWorkshop_DetailedReview_Adminhtml_Mageworkshop_Detailedreview_CustomerController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Mass ban (disable) customers to write review - Manage Customers page
     */
    public function massCustomerBanningAction()
    {
        $helper = Mage::helper('detailedreview');
        $customerIds = $this->getRequest()->getParam('customer');

        if (!is_array($customerIds)) {
            Mage::getSingleton('adminhtml/session')->addError($helper->__('Please select item(s)'));
        } else {
            /** @var Mage_Core_Model_Resource_Transaction $transaction */
            $transaction = Mage::getResourceSingleton('core/transaction');
            try {
                $customers = Mage::getModel('customer/customer')->getCollection();
                $customers->addFieldToFilter('entity_id', array('in' => $customerIds));
                $isBannedToWriteReview = (int) $this->getRequest()->getParam('is_banned_write_review');
                foreach ($customers as $customer) {
                    $customer->setIsBannedWriteReview($isBannedToWriteReview);
                    $transaction->addObject($customer);
                }
                Mage::dispatchEvent('detailedreview_adminhtml_customerbanning_save', array(
                    'customer'  => $customer,
                    'request'   => $this->getRequest()
                ));
                $transaction->save();
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    $helper->__('Total of %d record(s) were successfully saved', count($customerIds))
                );
            }
            catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }

        $this->_redirect('adminhtml/customer');
    }

    /**
     * Mass ban (disable) customers to write review - All Reviews page
     */
    public function massBanningAction()
    {
        $helper = Mage::helper('detailedreview');
        $reviewIds = $this->getRequest()->getParam('reviews');

        if (!is_array($reviewIds)) {
            Mage::getSingleton('adminhtml/session')->addError($helper->__('Please select item(s)'));
        } else {
            try {
                /** @var Mage_Review_Model_Resource_Review_Collection $reviews */
                $reviews = Mage::getModel('review/review')->getCollection();
                $reviews->addFieldToFilter('main_table.review_id', array('in' => $reviewIds));
                /** @var Mage_Review_Model_Review $review */
                /** @var MageWorkshop_DetailedReview_Model_AuthorIps $authorIpModel */
                foreach ($reviews as $review) {
                    $authorIp = null;
                    if ($customerId = $review->getCustomerId()) {
                        $customer = Mage::getModel('customer/customer')->load($customerId);
                        $customer->setIsBannedWriteReview(1)->save();
                        $authorIpModel = Mage::getModel('detailedreview/authorIps')->load($customerId, 'customer_id');
                    } else {
                        if (!$authorIp = $review->getRemoteAddr()) {
                            continue;
                        }
                        $authorIpModel = Mage::getModel('detailedreview/authorIps')->load($authorIp, 'remote_addr');
                    }

                    $date = Mage::app()->getLocale()->date();
                    $date->addDay($this->getRequest()->getParam('ban_author_for'));

                    $authorIpModel->setExpirationTime(Mage::getSingleton('core/date')->gmtDate(null, $date->getTimestamp()));
                    if (!$authorIpModel->getId()) {
                        $authorIpModel->setRemoteAddr($authorIp)
                                      ->setCustomerId($customerId);
                    }
                    Mage::dispatchEvent('detailedreview_adminhtml_customer_authorip_save', array(
                        'data'  => $authorIpModel,
                        'request'   => $this->getRequest()
                    ));
                    $authorIpModel->save();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    $helper->__('Total of %d record(s) were processed', count($reviewIds))
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }

        $this->_redirect('adminhtml/catalog_product_review');
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('catalog/reviews_ratings/reviews/all');
    }
}

