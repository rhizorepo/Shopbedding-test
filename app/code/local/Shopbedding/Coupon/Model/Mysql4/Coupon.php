<?php
class Shopbedding_Coupon_Model_Mysql4_Coupon extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        $this->_init('coupon/coupon', 'coupon_id');
    }
}