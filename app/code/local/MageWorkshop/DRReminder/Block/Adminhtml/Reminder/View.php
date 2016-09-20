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

class MageWorkshop_DRReminder_Block_Adminhtml_Reminder_View extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        $this->_objectId    = 'id';
        $this->_controller  = 'adminhtml_reminder';
        $this->_mode        = 'view';
        $this->_blockGroup = 'drreminder';

        parent::__construct();

        $this->_removeButton('save');
        $this->_removeButton('reset');
        $this->setId('reminder_view');

        if ($this->_isAllowedAction('hold')) {
            $message = Mage::helper('drreminder')->__('Hold this Review Reminder?');
            $this->_addButton('reminder_hold', array(
                'label'     => Mage::helper('drreminder')->__('Hold'),
                'onclick'   => 'deleteConfirm(\''.$message.'\', \'' . $this->getHoldUrl() . '\')',
            ));
        }
        if ($this->_isAllowedAction('unhold')) {
            $message = Mage::helper('drreminder')->__('Unhold this Review Reminder?');
            $this->_addButton('reminder_unhold', array(
                'label'     => Mage::helper('drreminder')->__('Unhold'),
                'onclick'   => 'deleteConfirm(\''.$message.'\', \'' . $this->getUnholdUrl() . '\')',
            ));
        }
        if ($this->_isAllowedAction('send')) {
            $message = Mage::helper('drreminder')->__('Are you sure you want to sent this Review Reminder immediately?');
            $this->_addButton('reminder_send', array(
                'label'     => Mage::helper('drreminder')->__('Send Now'),
                'onclick'   => 'confirmSetLocation(\''.$message.'\', \'' . $this->getSendUrl() . '\')',
            ));
        }
    }

    public function getReminder()
    {
        return Mage::registry('drreminder_reminder');
    }

    public function getReminderId()
    {
        return $this->getReminder()->getId();
    }

    public function getHeaderText()
    {
        return Mage::helper('drreminder')->__('View Reminder #%s Details', $this->getReminder()->getId());
    }

    public function getUrl($params='', $params2=array())
    {
        $params2['id'] = $this->getReminderId();
        return parent::getUrl($params, $params2);
    }

    public function getHoldUrl()
    {
        return $this->getUrl('*/*/hold');
    }

    public function getUnholdUrl()
    {
        return $this->getUrl('*/*/unhold');
    }

    public function getSendUrl()
    {
        return $this->getUrl('*/*/send');
    }


    protected function _isAllowedAction($action)
    {
        return Mage::getSingleton('admin/session')->isAllowed('drreminder/reminder/'.$action);
    }

}
