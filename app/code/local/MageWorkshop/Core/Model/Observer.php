<?php
/**
 * MageWorkshop
 * Copyright (C) 2016 MageWorkshop <mageworkshophq@gmail.com>
 *
 * @category   MageWorkshop
 * @package    MageWorkshop_Core
 * @copyright  Copyright (c) 2016 MageWorkshop Co. (http://mage-workshop.com)
 * @license    http://opensource.org/licenses/gpl-3.0.html GNU General Public License, version 3 (GPL-3.0)
 * @author     MageWorkshop <mageworkshophq@gmail.com>
 */

class MageWorkshop_Core_Model_Observer
{

    public function uninstallModule($observer)
    {
        $moduleConfig = $observer->getEvent()->getModuleConfig();
        if ($moduleConfig->getModuleName() == 'MageWorkshop_Core') {
            $uninstaller = Mage::getModel('drcore/uninstall');
            if ($uninstaller->checkPackageFile('Core')) {
                    $moduleConfig->setPackageName('Core');
            } else {
                $moduleConfig->setException(Mage::helper('drcore')->__('Cannot find package file for MageWorkshop Core plugin.'));
            }
        }
    }

}
