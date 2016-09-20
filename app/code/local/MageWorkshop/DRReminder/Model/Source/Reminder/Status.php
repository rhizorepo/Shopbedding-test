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

class MageWorkshop_DRReminder_Model_Source_Reminder_Status
{
    const REMINDER_STATUS_PENDING = 0;
    const REMINDER_STATUS_SENT  = 1;
    const REMINDER_STATUS_ON_HOLD  = 2;

    public static function toOptionArray()
    {
        return array(
            self::REMINDER_STATUS_PENDING  => Mage::helper('drreminder')->__('Pending'),
            self::REMINDER_STATUS_SENT => Mage::helper('drreminder')->__('Sent'),
            self::REMINDER_STATUS_ON_HOLD => Mage::helper('drreminder')->__('On Hold'),
        );
    }

}
