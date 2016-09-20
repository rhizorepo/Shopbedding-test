<?php

class Shopbedding_UpdateFeed_Adminhtml_CronController extends Mage_Adminhtml_Controller_Action
{
    public function runUpdateFeedCronAction()
    {
        if (Mage::getModel('shopbedding_updatefeed/update')->doUpdate(true)) {
            $message = Mage::helper('core')->__('Cron finished work. Feed has been updated');
            Mage::getSingleton('core/session')->addSuccess($message);
            $this->_redirect('adminhtml/system_config/edit/section/shopbedding_update_feed');
        } else {
            $message = Mage::helper('core')->__('An error occurred while running cron');
            Mage::getSingleton('core/session')->addError($message);
            $this->_redirect('adminhtml/system_config/edit/section/shopbedding_update_feed');
        }
    }
}