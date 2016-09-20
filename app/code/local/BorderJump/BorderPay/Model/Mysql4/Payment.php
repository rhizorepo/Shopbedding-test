<?php
 
class BorderJump_BorderPay_Model_Mysql4_Payment extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {   
        $this->_init('borderpay/payment', 'id');
    }
}
