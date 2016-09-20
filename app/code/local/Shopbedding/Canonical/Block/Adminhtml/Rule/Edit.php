<?php

class Shopbedding_Canonical_Block_Adminhtml_Rule_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
                 
        $this->_objectId = 'id';
        $this->_mode = 'edit';
        $this->_blockGroup = 'shop_canonical';
        $this->_controller = 'adminhtml_rule';

		$this->_updateButton('save', 'label', Mage::helper('shop_canonical')->__('Save Rule'));
		
		$this->_updateButton('delete', 'label', Mage::helper('shop_canonical')->__('Delete Rule'));

        $id = (int)$this->getRequest()->getParam($this->_objectId);
        $rule = Mage::getModel('shop_canonical/rule')->load($id);
        Mage::register('current_rule', $rule);

    }

    public function getHeaderText()
    {
        if( Mage::registry('current_rule') && Mage::registry('current_rule')->getId() ) {
            return Mage::helper('shop_canonical')->__('Edit Rule');
        } else {
            return Mage::helper('shop_canonical')->__('Create Rule');
        }
    }

}