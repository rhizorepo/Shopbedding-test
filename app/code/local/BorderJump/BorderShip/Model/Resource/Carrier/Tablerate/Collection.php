<?php

class BorderJump_BorderShip_Model_Resource_Carrier_Tablerate_Collection extends Mage_Shipping_Model_Mysql4_Carrier_Tablerate_Collection
{
    protected function _construct() {
        $this->_init('bordership/carrier_tablerate');
        $this->_shipTable       = $this->getMainTable();
        $this->_countryTable    = $this->getTable('directory/country');
        $this->_regionTable     = $this->getTable('directory/country_region');
    }
}
