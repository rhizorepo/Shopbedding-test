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
class MageWorkshop_DetailedReview_HelpfulController extends Mage_Core_Controller_Front_Action
{
    /**
     * Vote for review - is it helpful or not
     */
    public function voteAction()
    {
        $params = $this->getRequest()->getParams();
        $helper = Mage::helper('detailedreview');
        $response = array(
            'msg' => array(
                'type' => 'notice',
                'text' => $helper->__('Service Temporarily Unavailable')
            )
        );
        if (!empty($params) && $reviewId = (int) $this->getRequest()->getParam('review_id')) {
            /** @var MageWorkshop_DetailedReview_Model_Review_Helpful $helpful */
            $helpful = Mage::getModel('detailedreview/review_helpful')->setData($params);

            if (Mage::getSingleton('customer/session')->IsLoggedIn()) {
                $helpful->setCustomerId(Mage::getSingleton('customer/session')->getCustomerId());
            }
            $helpful->setRemoteAddr(Mage::helper('core/http')->getRemoteAddr());
            if (Mage::getStoreConfig('detailedreview/settings_customer/allow_guest_vote')) {
                $helpful->setRemoteAddr(Mage::helper('core/http')->getRemoteAddr());
            }

            if ($validationErrors = $helpful->validate()) {
                $response = array(
                    'msg' => array(
                        'type' => 'error',
                        'text' => $helper->__('Unable to add your vote. %s', implode(', ', $validationErrors))
                    )
                );
            } else {
                try {
                    $helpful->save();
                    $helpfulVotes = $helpful->getQtyHelpfulVotesForReview($reviewId);
                    $unhelpfulVotes = $helpful->getQtyVotesForReview($reviewId) - $helpfulVotes;
                    $response = array(
                        'helpful'   => $helpfulVotes,
                        'unhelpful' => $unhelpfulVotes,
                        'msg'       => array(
                            'type' => 'success',
                            'text' => $helper->__('Your vote has been added successfully.')
                        )
                    );
                } catch (Exception $e) {
                    $response = array(
                        'msg' => array(
                            'type' => 'error',
                            'text' => $helper->__('Unable to add your vote.')
                        )
                    );
                }
            }
        }
        $this->getResponse()->setBody(json_encode($response));
    }
}
