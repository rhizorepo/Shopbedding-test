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
class MageWorkshop_Core_Adminhtml_Mageworkshop_Core_MainController extends Mage_Adminhtml_Controller_Action
{

    public function enableAction()
    {
        $moduleName = $this->getRequest()->getParam('package');
        $moduleConfig = new Varien_Object();
        $moduleConfig->setModuleName($moduleName);
        Mage::dispatchEvent('mageworkshop_module_enable', array('module_config' => $moduleConfig));
        if($result = $moduleConfig->getEnabled()) {
            Mage::getSingleton('core/session')->addSuccess(Mage::helper('drcore')->__('%s extension has been %s.', $moduleName, $result));
        } else {
            Mage::getSingleton('core/session')->addError(Mage::helper('drcore')->__('There was a problem with changing module configuration.'));
        }
        $this->_redirectReferer();
    }

    /**
     * Uninstall extension
     */
    public function uninstallAction()
    {
        $session = Mage::getSingleton('core/session');
        $helper = Mage::helper('drcore');
        $moduleName = $this->getRequest()->getParam('package');
        $moduleConfig = new Varien_Object();
        $moduleConfig->setModuleName($moduleName);
        Mage::dispatchEvent('mageworkshop_module_uninstall', array('module_config' => $moduleConfig));
        if($packageName = $moduleConfig->getPackageName()) {
            try {
                Mage::getModel('drcore/uninstall')->processUninstallPackage($packageName);
                if($dependent = $moduleConfig->getDependentPackage()) {
                    Mage::getModel('drcore/uninstall')->processUninstallPackage($dependent);
                    Mage::getModel('drcore/uninstall')->processUninstallPackage($moduleConfig->getParentPackage());
                    $packageName = $moduleConfig->getParentPackage();
                }
                Mage::app()->cleanCache();
                Mage::app()->getConfig()->reinit();
                $session->addSuccess($helper->__('%s extension has been completely uninstalled.', $packageName));
            } catch (Mage_Core_Exception $e) {
                $session->addException($e, $helper->__('There was a problem with uninstalling: %s', $e->getMessage()));
            } catch (Exception $e) {
                $session->addException($e, $helper->__('There was a problem with uninstalling.'));
            }
        } else {
            Mage::getSingleton('core/session')->addError(Mage::helper('drcore')->__('There was a problem with uninstalling.'));
        }
        $this->_redirect('adminhtml/system_config/index');
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('system/config/detailedreview');
    }
}
