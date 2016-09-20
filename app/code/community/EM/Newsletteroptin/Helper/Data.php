<?php

class EM_Newsletteroptin_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function isEnabled()
    {
        return Mage::getStoreConfigFlag('newsletter/newsletteroptin/enable');
    }

    public function isChecked()
    {
        return Mage::getStoreConfigFlag('newsletter/newsletteroptin/checked');
    }

    public function isVisibleGuest()
    {
        return Mage::getStoreConfigFlag('newsletter/newsletteroptin/visible_guest');
    }

    public function isVisibleRegister()
    {
        return Mage::getStoreConfigFlag('newsletter/newsletteroptin/visible_register');
    }
}