<?php
class Shopbedding_Coupon_Model_Coupon extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('coupon/coupon');
    }
}