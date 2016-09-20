<?php

class Shopbedding_Canonical_Block_Adminhtml_Rule_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
	protected function _prepareForm()
	{
	  $form = new Varien_Data_Form();
	  $this->setForm($form);
	  $fieldset = $form->addFieldset('redirect_form', array('legend' => Mage::helper('shop_canonical')->__('Rule')));
	  
	  $fieldset->addField('source', 'text', array(
	      'label'     => Mage::helper('shop_canonical')->__('From URL'),
	      'class'     => 'required-entry',
	      'required'  => true,
	      'name'      => 'source',
	  ));

      $fieldset->addField('target', 'text', array(
            'label'     => Mage::helper('shop_canonical')->__('To URL'),
            'class'     => 'required-entry',
            'required'  => true,
            'name'      => 'target',
        ));

      if(($rule = Mage::registry('current_rule'))){
          $form->setValues($rule->getData());
      }

	  return parent::_prepareForm();
	}
}