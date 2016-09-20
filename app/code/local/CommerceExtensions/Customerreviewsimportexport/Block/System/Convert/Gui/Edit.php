<?php

class CommerceExtensions_Customerreviewsimportexport_Block_System_Convert_Gui_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        $this->_objectId = 'id';
    	$this->_blockGroup = 'customerreviewsimportexport';
        $this->_controller = 'customerreviewsimportexport';

        parent::__construct();

        $this->_updateButton('save', 'label', Mage::helper('customerreviewsimportexport')->__('Save Profile'));
        $this->_updateButton('delete', 'label', Mage::helper('customerreviewsimportexport')->__('Delete Profile'));
        $this->_addButton('savecontinue', array(
            'label' => Mage::helper('customerreviewsimportexport')->__('Save and Continue Edit'),
            'onclick' => "$('edit_form').action += 'continue/true/'; editForm.submit();",
            'class' => 'save',
        ), -100);
    }

    public function getProfileId()
    {
        return Mage::registry('current_convert_profile')->getId();
    }

    public function getHeaderText()
    {
        if (Mage::registry('current_convert_profile')->getId())
        {
            return $this->htmlEscape(Mage::registry('current_convert_profile')->getName());
        }
        else
        {
            return Mage::helper('customerreviewsimportexport')->__('New Profile');
        }
    }
}
