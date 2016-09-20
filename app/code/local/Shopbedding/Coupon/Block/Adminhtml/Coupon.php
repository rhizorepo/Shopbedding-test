<?php
class Shopbedding_Coupon_Block_Adminhtml_Coupon extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    $this->_controller = 'adminhtml_coupon';
    $this->_blockGroup = 'coupon';
    $this->_headerText = Mage::helper('coupon')->__('Coupon Export');
    parent::__construct();
	$this->_removeButton('add');
  }
}