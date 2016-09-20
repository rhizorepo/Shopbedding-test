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

class MageWorkshop_DRReminder_Block_Adminhtml_Reminder_View_Form extends Mage_Adminhtml_Block_Template
{
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('mageworkshop/drreminder/reminder-view.phtml');
    }

    public function getReminder()
    {
        return Mage::registry('drreminder_reminder');
    }

    public function getEditUrl($id)
    {
        return $this->getUrl('*/*/edit', array('id' => $id));
    }

    public function getOrderInfo($orderId)
    {
        return Mage::getModel('sales/order')->load($orderId);
    }

    public function getStatusLabel($statusCode)
    {
        $statuses = MageWorkshop_DRReminder_Model_Source_Reminder_Status::toOptionArray();
        return $statuses[$statusCode];
    }


}
