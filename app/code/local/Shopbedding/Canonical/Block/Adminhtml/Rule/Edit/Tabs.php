<?php

class Shopbedding_Canonical_Block_Adminhtml_Rule_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

  public function __construct()
  {
      parent::__construct();
      $this->setId('canonical_tabs');
      $this->setDestElementId('edit_form');
      $this->setTitle(Mage::helper('shop_canonical')->__('Rule config'));
  }

  protected function _beforeToHtml()
  {
      $this->addTab('form_section', array(
          'label'     => Mage::helper('shop_canonical')->__('Rule config'),
          'title'     => Mage::helper('shop_canonical')->__('Rule config'),
          'content'   => $this->getLayout()->createBlock('shop_canonical/adminhtml_rule_edit_tab_form')->toHtml(),
      ));
     
      return parent::_beforeToHtml();
  }
}