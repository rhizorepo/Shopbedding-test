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
class MageWorkshop_DetailedReview_Adminhtml_Mageworkshop_Detailedreview_MainController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Reindex sorting attribute
     */
    public function reindexAction()
    {
        $helper = Mage::helper('detailedreview');
        try {
            if (Mage::getStoreConfig('detailedreview/settings/deny_change_polarity_group')) {
                Mage::getModel('detailedreview/sort')->refreshAllIndices();
            } else {
                throw new Exception('Reindex disabled, please check config Detailed Review -> Settings -> Deny Administrator to change Popularity');
            }

            Mage::getSingleton('adminhtml/session')->addSuccess($helper->__('The data were indexed'));
            $this->_redirectReferer();
        } catch (Exception $e) {
            $this->_redirectReferer();
            Mage::getSingleton('adminhtml/session')->addError($helper->__('Reindex was failed. %s', $e->getMessage()));
        }
    }

    public function updatePurchaseAction()
    {
        $helper = Mage::helper('detailedreview');
        try {
            Mage::getModel('detailedreview/purchase')->updateData();
            Mage::getSingleton('adminhtml/session')->addSuccess($helper->__('Data about purchases were updated.'));
            $this->_redirectReferer();
        } catch (Exception $e) {
            $this->_redirectReferer();
            Mage::getSingleton('adminhtml/session')->addError($helper->__('Failure to update the purchase data. %s', $e->getMessage()));
        }
    }

    protected function _isAllowed()
    {
        return true;
    }
}
