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

class MageWorkshop_DRReminder_Model_Uninstall extends Mage_Core_Model_Abstract
{
    public function clearDRReminderSource()
    {
        $setup = new Mage_Eav_Model_Entity_Setup('core_setup');

        $setup->startSetup();
        $coreResource = Mage::getSingleton('core/resource');
        $reviewRemindersTable = $coreResource->getTableName('drreminder/review_reminders');
        $salesFlatOrderItemTable = $coreResource->getTableName('sales/order_item');
        $sql = "DROP TABLE IF EXISTS `$reviewRemindersTable`;";
        $setup->run($sql);
        $coreResource->getConnection('core_write')->dropColumn($salesFlatOrderItemTable,'reminder');
        $setup->endSetup();
    }

}
