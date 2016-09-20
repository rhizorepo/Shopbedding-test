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

class MageWorkshop_DRReminder_Block_Adminhtml_Reminder_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('drreminder_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle($this->__('Reminder Information'));
    }
    
    protected function _beforeToHtml()
    {
        $this->addTab('general', array(
                'label'   => $this->__('General'),
                'title'   => $this->__('General'),
                'content' => $this->getLayout()->createBlock('drreminder/adminhtml_reminder_edit_tab_form')->toHtml()
            )
        );

        return parent::_beforeToHtml();
    }

    public function getReminder(){
        return Mage::registry('drreminder_reminder');
    }
}
