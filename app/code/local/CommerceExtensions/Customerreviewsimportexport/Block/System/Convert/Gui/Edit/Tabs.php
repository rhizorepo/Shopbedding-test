<?php

class CommerceExtensions_Customerreviewsimportexport_Block_System_Convert_Gui_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('convert_profile_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('customerreviewsimportexport')->__('Import/Export Profile'));
    }

    protected function _beforeToHtml()
    {
        $profile = Mage::registry('current_convert_profile');
        
        $wizardBlock = $this->getLayout()->createBlock('customerreviewsimportexport/system_convert_gui_edit_tab_wizard');
        $wizardBlock->addData($profile->getData());

        $new = !$profile->getId();
        
        $this->addTab('wizard', array(
            'label'     => Mage::helper('customerreviewsimportexport')->__('Profile Wizard'),
            'content'   => $wizardBlock->toHtml(),
            'active'    => true
        ));

        if (!$new)
        {
            if ($profile->getDirection() != 'export')
            {
                $this->addTab('upload', array(
                    'label'     => Mage::helper('customerreviewsimportexport')->__('Upload File'),
                    'content'   => $this->getLayout()->createBlock('customerreviewsimportexport/system_convert_gui_edit_tab_upload')->toHtml()
                ));
            }

            $this->addTab('run', array(
                'label'     => Mage::helper('customerreviewsimportexport')->__('Run Profile'),
                'content'   => $this->getLayout()->createBlock('adminhtml/system_convert_profile_edit_tab_run')->toHtml()
            ));
            
            $this->addTab('view', array(
                'label'     => Mage::helper('customerreviewsimportexport')->__('Profile Actions XML'),
                'content'   => $this->getLayout()->createBlock('customerreviewsimportexport/system_convert_gui_edit_tab_view')->initForm()->toHtml()
            ));

            $this->addTab('history', array(
                'label'     => Mage::helper('customerreviewsimportexport')->__('Profile History'),
                'content'   => $this->getLayout()->createBlock('adminhtml/system_convert_profile_edit_tab_history')->toHtml()
            ));
        }

        return parent::_beforeToHtml();
    }
}
