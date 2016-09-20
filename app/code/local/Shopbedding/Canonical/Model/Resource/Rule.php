<?php

class Shopbedding_Canonical_Model_Resource_Rule extends    Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init('shop_canonical/rule', 'id');
    }

}