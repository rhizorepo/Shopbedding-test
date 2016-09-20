<?php

class BorderJump_BorderShip_Model_Resource_Carrier_Tablerate extends Mage_Shipping_Model_Mysql4_Carrier_Tablerate
{
    protected function _construct()
    {
        $this->_init('bordership/carrier_tablerate', 'id');
    }
}
