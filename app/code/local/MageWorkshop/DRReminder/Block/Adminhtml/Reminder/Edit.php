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


class MageWorkshop_DRReminder_Block_Adminhtml_Reminder_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
        
        $this->_objectId = 'id';
        $this->_blockGroup = 'drreminder';
        $this->_controller = 'adminhtml_reminder';

        $this->_updateButton('save', 'label', $this->__('Save'));
        $this->_updateButton('delete', 'label', $this->__('Delete'));

    }

    public function getHeaderText()
    {
        $reminder = $this->getReminder();
        if ( $reminder->getIsNewObject()  ) {
            return $this->__('Add Reminder');
        }
        return $this->__("Edit Reminder");
    }

    public function getReminder(){
        return Mage::registry('drreminder_reminder');
    }
}
