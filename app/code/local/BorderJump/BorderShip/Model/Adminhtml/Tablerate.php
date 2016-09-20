<?php

class BorderJump_BorderShip_Model_Adminhtml_Tablerate extends Mage_Core_Model_Config_Data
{
    public function _afterSave()
    {
        Mage::getResourceModel('bordership/carrier_tablerate')->uploadAndImport($this);
    }
}
