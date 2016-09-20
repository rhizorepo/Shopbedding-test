<?php
/**
 * MageWorkshop
 * Copyright (C) 2016 MageWorkshop <mageworkshophq@gmail.com>
 *
 * @category   MageWorkshop
 * @package    MageWorkshop_DetailedReview
 * @copyright  Copyright (c) 2016 MageWorkshop Co. (http://mage-workshop.com)
 * @license    http://opensource.org/licenses/gpl-3.0.html GNU General Public License, version 3 (GPL-3.0)
 * @author     MageWorkshop <mageworkshophq@gmail.com>
 */
class MageWorkshop_DetailedReview_Block_Adminhtml_System_Config_License extends Mage_Adminhtml_Block_System_Config_Form_Fieldset
{
    /**
     * @param $field
     * @return string
     */
    protected function _getTableContent($field)
    {
        $helper = Mage::helper('core');
        $config = Mage::getConfig();
        $storeLink = Mage::getStoreConfig('detailedreview/store_link');
        if (Mage::helper('detailedreview')->checkLicenseKey()) {
            $keyHtml = "<span style=\"color:green;\">{$helper->__('Valid')}</span>";
        } else {
            $keyHtml = "<span style=\"color:red\">{$helper->__('Not Valid')}</span>
                        <a target='_blank' href=\"$storeLink\"{$helper->__('Buy License')}</a>";
        }

        $html = "<tr>
                    <td>Detailed Review {$config->getNode()->modules->MageWorkshop_DetailedReview->version[0]}</td>
                    <td><table>{$field->getElementHtml()}</table></td>
                    <td>$keyHtml</td>";
        $html .= $this->checkConfigInherit($field);

        return $html;
    }

    /**
     * @return string
     */
    protected function _getTableHtml()
    {
        $html = '<style>
            table.detailedreview {
                border:1px solid #D6D6D6;
                border-collapse: collapse;
                padding:8px;
                text-align: center;
            }
            .detailedreview td {
                border: 1px solid #D6D6D6;
                padding:8px;
            }
        </style>';
        $helper = Mage::helper('core');
        $html .= "<table class=\"detailedreview\">
                    <tr>
                        <td>{$helper->__('Module Name')}</td>
                        <td>{$helper->__('License Key')}</td>
                        <td>{$helper->__('License Status')}</td>
                        <td colspan=\"2\">{$helper->__('Inherit config')}</td>
                    </tr>";
        return $html;
    }

    /**
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $html = $this->_getHeaderHtml($element);
        $html .= $this->_getTableHtml();
        foreach ($element->getSortedElements() as $field) {
            $html .= $this->_getTableContent($field);
        }
        $html .= $this->_getFooterHtml($element);

        return $html;
    }

    public function checkConfigInherit(Varien_Data_Form_Element_Abstract $element)
    {
        $id = $element->getHtmlId();

        $isMultiple = $element->getExtType() === 'multiple';

        // replace [value] with [inherit]
        $namePrefix = preg_replace('#\[value\](\[\])?$#', '', $element->getName());

        $options = $element->getValues();

        $addInheritCheckbox = false;
        if ($element->getCanUseWebsiteValue()) {
            $addInheritCheckbox = true;
            $checkboxLabel = Mage::helper('adminhtml')->__('Use Website');
        }
        elseif ($element->getCanUseDefaultValue()) {
            $addInheritCheckbox = true;
            $checkboxLabel = Mage::helper('adminhtml')->__('Use Default');
        }

        if ($addInheritCheckbox) {
            $inherit = $element->getInherit() == 1 ? 'checked="checked"' : '';
            if ($inherit) {
                $element->setDisabled(true);
            }
        }

        $html = '';
        if ($addInheritCheckbox) {
            $defText = $element->getDefaultValue();
            if ($options) {
                $defTextArr = array();
                foreach ($options as $k=>$v) {
                    if ($isMultiple) {
                        if (is_array($v['value']) && in_array($k, $v['value'])) {
                            $defTextArr[] = $v['label'];
                        }
                    } elseif ($v['value']==$defText) {
                        $defTextArr[] = $v['label'];
                        break;
                    }
                }
                $defText = join(', ', $defTextArr);
            }

            // default value
            $html = "<td class=\"use-default\">
                     <input id=\"{$id}_inherit\" name=\"{$namePrefix}[inherit]\" type=\"checkbox\" value=\"1\" class=\"checkbox config-inherit\" {$inherit} onclick=\"toggleValueElements(this, Element.previous(this.parentNode))\" />
                     <label for=\"{$id}_inherit\" class=\"inherit\" title=\"{htmlspecialchars($defText)}\">{$checkboxLabel}</label>
                     </td>";
        }

        $html.= '<td class="scope-label">';
        if ($element->getScope()) {
            $html .= $element->getScopeLabel();
        }
        $html.= '</td></tr>';

        return $html;
    }


}
