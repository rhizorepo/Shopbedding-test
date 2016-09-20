<?php

class CommerceExtensions_Customerreviewsimportexport_Block_System_Convert_Gui extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_controller = 'system_convert_gui';
        $this->_blockGroup = 'customerreviewsimportexport';
        
        $this->_headerText = Mage::helper('customerreviewsimportexport')->__('Profiles');
        $this->_addButtonLabel = Mage::helper('customerreviewsimportexport')->__('Add New Profile');

        parent::__construct();
    }
}