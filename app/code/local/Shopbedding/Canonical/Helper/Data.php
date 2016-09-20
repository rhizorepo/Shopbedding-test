<?php

class Shopbedding_Canonical_Helper_Data extends Mage_Core_Helper_Abstract
{
    const XML_PATH_IS_ENABLED = 'shop_canonical_general/config/enabled';

    public function isEnabled()
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_IS_ENABLED);
    }
}