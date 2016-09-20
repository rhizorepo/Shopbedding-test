<?php
class Shopbedding_Coupon_Block_Coupon extends Mage_Core_Block_Template
{
	public function _prepareLayout()
    {
		return parent::_prepareLayout();
    }
    
     public function getCoupon()     
     { 
        if (!$this->hasData('coupon')) {
            $this->setData('coupon', Mage::registry('coupon'));
        }
        return $this->getData('coupon');
        
    }
}