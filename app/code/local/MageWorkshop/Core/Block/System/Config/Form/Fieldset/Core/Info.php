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

class MageWorkshop_Core_Block_System_Config_Form_Fieldset_Core_Info extends Mage_Adminhtml_Block_System_Config_Form_Fieldset
{

    protected $_fieldRenderer;

    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $html = $this->_getHeaderHtml($element);

        $modules = array_keys((array)Mage::getConfig()->getNode('modules')->children());

        sort($modules);

        foreach ($modules as $moduleName) {
            if (strstr($moduleName,'MageWorkshop_') === false) {
                continue;
            }
            if ($moduleName==='MageWorkshop_Core' || !Mage::helper('core')->isModuleEnabled($moduleName)) {
                continue;
            }
            $html.= $this->_getFieldHtml($element, $moduleName);
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
        $version = Mage::getConfig()->getModuleConfig($moduleName)->version;
        $id = $moduleName . $version;
        $configFile = Mage::getConfig()->getModuleDir('etc', $moduleName).DS.'config.xml';
        $xmlObj = new Varien_Simplexml_Config($configFile);
        $xmlData = $xmlObj->getNode()->asCanonicalArray();
        $section = '*/system_config/edit/section/'. key($xmlData['global']['blocks']);
        $url = Mage::helper('adminhtml')->getUrl($section);
        $field = $fieldset->addField($id, 'link',
            array(
                'name'      => $moduleName,
                'href'      => $url,
                'value'     => $version,
                'label'     => $moduleName,
                'title'     => $moduleName,
            ))->setRenderer($this->_getFieldRenderer());
        return $field->toHtml();
    }
}
