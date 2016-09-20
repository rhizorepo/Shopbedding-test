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

class MageWorkshop_DRReminder_Model_Observer
{

    public function initCreateReviewReminder($observer)
    {
        if(Mage::getStoreConfig('drreminder/settings/remind_enable')) {
            $order = $observer->getEvent()->getData('order');
            Mage::helper('drreminder')->createReviewReminder($order);
        }
    }

    public function checkIfModuleEnabled($observer)
    {
        $moduleContainer = $observer->getEvent()->getModuleContainer();
        if ($moduleContainer->getModule() == 'MageWorkshop_DRReminder') {
            $storeId = Mage::app()->getStore()->getId();
            $moduleContainer->setEnabled(Mage::getStoreConfig('drreminder/settings/remind_enable', $storeId));
        }
    }

    public function enableModule($observer)
    {
        $moduleConfig = $observer->getEvent()->getModuleConfig();
        if ($moduleConfig->getModuleName() == 'MageWorkshop_DRReminder') {
            $storeId = Mage::app()->getStore()->getId();
            if(Mage::getStoreConfig('drreminder/settings/remind_enable', $storeId)) {
                Mage::getModel('core/config')->saveConfig('drreminder/settings/remind_enable', 0);
                $moduleConfig->setEnabled('disabled');
            } else {
                Mage::getModel('core/config')->saveConfig('drreminder/settings/remind_enable', 1);
                $moduleConfig->setEnabled('enabled');
            }

        }
    }

    public function uninstallModule($observer)
    {
        $moduleConfig = $observer->getEvent()->getModuleConfig();
        if ($moduleConfig->getModuleName() == 'MageWorkshop_DRReminder') {
            $uninstaller = Mage::getModel('drcore/uninstall');
            if ($uninstaller->checkPackageFile('DRReminder')) {
                try {
                    Mage::getModel('drreminder/uninstall')->clearDRReminderSource();
                    $moduleConfig->setPackageName('DRReminder');
                } catch (Mage_Core_Exception $e) {
                    $moduleConfig->setException($e->getMessage());
                } catch (Exception $e) {
                    $moduleConfig->setException(Mage::helper('drreminder')->__('There was a problem with uninstalling.'));
                }
            } else {
                $moduleConfig->setException(Mage::helper('drreminder')->__('Cannot find package file for Detailed Review Reminder plugin.'));
            }
        }
    }

}
