<?php
class Shopbedding_Canonical_Block_Adminhtml_Rule extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function _construct()
  {
    parent::_construct();
    $this->_controller = 'adminhtml_rule';
    $this->_blockGroup = 'shop_canonical';
    $this->_headerText = Mage::helper('shop_canonical')->__('Canonical rules');
  }
}