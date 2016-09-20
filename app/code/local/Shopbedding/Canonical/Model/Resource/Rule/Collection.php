<?php

class Shopbedding_Canonical_Model_Resource_Rule_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    public function _construct()
    {
        $this->_init('shop_canonical/rule');
    }
}