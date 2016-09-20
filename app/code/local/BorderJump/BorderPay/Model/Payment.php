<?php
 
class BorderJump_BorderPay_Model_Payment extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('borderpay/payment');
    }
    
    public function setMagePayment($payment) {
        $this->setPaymentId($payment->getEntityId());
    }
    
    public function loadByMagePayment($payment) {
        return $this->load($payment->getEntityId(), 'payment_id');
    }
    
    public function loadByOrderNumber($number) {
        return $this->load($number, 'order_number');
    }
    
    public function getMagePayment() {
        return Mage::getModel('sales/order_payment')->load($this->getPaymentId());
    }
    
    public function getMageOrder() {
        $payment = Mage::getModel('sales/order_payment')->load($this->getPaymentId());
        return Mage::getModel('sales/order')->load($payment->getParentId());
    }
}
