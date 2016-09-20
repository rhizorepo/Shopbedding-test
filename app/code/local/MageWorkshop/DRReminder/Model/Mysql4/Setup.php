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

class MageWorkshop_DRReminder_Model_Mysql4_Setup extends Mage_Catalog_Model_Resource_Eav_Mysql4_Setup
{
    protected function _upgradeData($oldVersion, $newVersion)
    {
        parent::_upgradeData($oldVersion, $newVersion);
        /** @var MageWorkshop_Core_Helper_Data $helper */
        $helper = Mage::helper('drcore');
        $helper->clearCacheAfterInstall()
            ->reindexDataAfterInstall();
        return $this;
    }
}

