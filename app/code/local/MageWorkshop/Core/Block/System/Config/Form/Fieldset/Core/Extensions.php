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

class MageWorkshop_Core_Block_System_Config_Form_Fieldset_Core_Extensions extends Mage_Adminhtml_Block_System_Config_Form_Fieldset
{

    protected $_fieldRenderer;

    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $html = $this->_getHeaderHtml($element);
        $modules = array_keys((array)Mage::getConfig()->getNode('modules')->children());
        sort($modules);
        $count = 0;
        foreach ($modules as $moduleName) {
            if (strstr($moduleName,'MageWorkshop_') === false) {
                continue;
            }
            if ($moduleName === 'MageWorkshop_Core') {
                continue;
            }
            $count++;
            $enableLabel = $this->__("Enable");
            $moduleContainer = new Varien_Object();
            $moduleContainer->setModule($moduleName);
            Mage::dispatchEvent('mageworkshop_modules_before_load', array('module_container' => $moduleContainer));
            if($moduleContainer->getEnabled()) {
                $enableLabel = $this->__("Disable");
            }
            $html .= $this->_getFieldHtml($element, $moduleName);
            $html = substr($html, 0, strrpos($html, '<td class="value"'));
            if (Mage::helper('core')->isModuleEnabled($moduleName)) {
                $enableUrl = '\''. $this->getUrl('adminhtml/mageworkshop_core_main/enable', array('package' => $moduleName)).'\'';
                $uninstallUrl = '\''. $this->getUrl('adminhtml/mageworkshop_core_main/uninstall', array('package' => $moduleName)).'\'';
                $html .= '<td class="value"><a  href="#" onclick="setLocation('.$enableUrl.')">'. $enableLabel . '</a><a  href="#" style="margin-left:20px;" onclick="if(confirm(\''. $this->__("This will completely uninstall extension and delete all related information. Are you sure?").'\')){setLocation('.$uninstallUrl.');}">'. $this->__("Uninstall") . '</a></td>';
            } else {
                $html .= '<td class="value"><span>'.$this->__("It seems like module is disabled via Disable Modules Output.").'</span></td>';
            }

        }
        $html .= $this->_getFieldHtml($element, 'MageWorkshop_Core');
        if($count > 0) {
            $html = substr($html, 0, strRpos($html, '<td class="value"'));
            $html .= '<td class="value"><a  href="#" style="color: #cacaca;" onclick="alert(\''. $this->__("There are dependent MageWorkshop modules installed. You can not uninstall MageWorkshop Core module now.").'\')">'. $this->__("Uninstall") . '</a></td>';
        } else {
            $uninstallUrl = '\''. $this->getUrl('adminhtml/mageworkshop_core_main/uninstall', array('package' => 'MageWorkshop_Core')).'\'';
            $html = substr($html, 0, strrpos($html, '<td class="value"'));
            $html .= '<td class="value"><a  href="#" onclick="if(confirm(\''. $this->__("This will completely uninstall extension and delete all related information. Are you sure?").'\')){setLocation('.$uninstallUrl.');}">'. $this->__("Uninstall") . '</a></td>';
        }
        $html .= $this->_getFooterHtml($element);

        return $html;
    }

    protected function _getFieldRenderer()
    {
        if (empty($this->_fieldRenderer)) {
            $this->_fieldRenderer = Mage::getBlockSingleton('adminhtml/system_config_form_field');
        }
        return $this->_fieldRenderer;
    }


    protected function _getFieldHtml($fieldset, $moduleName)
    {
        $id = $moduleName;
        $field = $fieldset->addField($id, 'label',
            array(
                'name' => $id,
                'label' => $moduleName,
                'value' => $moduleName,
            ))->setRenderer($this->_getFieldRenderer());
        return $field->toHtml();
    }
}
