<?php
/**
 * MageWorkshop
 * Copyright (C) 2016 MageWorkshop <mageworkshophq@gmail.com>
 *
 * @category   MageWorkshop
 * @package    MageWorkshop_DRReminder
 * @copyright  Copyright (c) 2016 MageWorkshop Co. (http://mage-workshop.com)
 * @license    http://opensource.org/licenses/gpl-3.0.html GNU General Public License, version 3 (GPL-3.0)
 * @author     MageWorkshop <mageworkshophq@gmail.com>
 */

class MageWorkshop_DRReminder_Block_Adminhtml_Reminder_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm() 
    {
        $form = new Varien_Data_Form();
        $fieldset = $form->addFieldset('form', array('legend' => $this->__('Reminder')));

        $fieldset->addField('customer_name', 'text', array(
            'label'    => $this->__('Customer Name'),
            'name'     => 'customer_name',
            'required' => true,
        ));

        $fieldset->addField('email', 'text', array(
                'label'    => $this->__('Email'),
                'name'     => 'email',
                'required' => true,
            ));

        $form->setValues($this->getReminder()->getData());
        Mage::dispatchEvent('drreminder_adminhtml_reminder_edit_prepare_form', array('form' => $form));
        $this->setForm($form);

        return parent::_prepareForm();
    }

    public function getReminder(){
        return Mage::registry('drreminder_reminder');
    }
}
