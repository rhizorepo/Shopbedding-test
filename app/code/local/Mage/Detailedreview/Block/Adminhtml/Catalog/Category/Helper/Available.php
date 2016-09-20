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

/**
 * Class Mage_Detailedreview_Block_Adminhtml_Catalog_Category_Helper_Available
 *
 * @method string getValue()
 */
class Mage_Detailedreview_Block_Adminhtml_Catalog_Category_Helper_Available
    extends Varien_Data_Form_Element_Multiselect
{
    /**
     * Retrieve Element HTML fragment
     *
     * @return string
     */
    public function getElementHtml()
    {
        $disabled = (bool) !$this->getValue();
        if ($disabled) {
            $this->setData('disabled', 'disabled');
        }
        $html = parent::getElementHtml();
        $htmlId = 'use_config_' . $this->getHtmlId();
        $html .= '<input id="'.$htmlId.'" name="use_config[]" value="' . $this->getId() . '"';
        $html .= ($disabled ? ' checked="checked"' : '');

        if ($this->getReadonly()) {
            $html .= ' disabled="disabled"';
        }
        $html .= ' onclick="toggleValueElements(this, this.parentNode);" class="checkbox" type="checkbox" />';

        $html .= ' <label for="'.$htmlId.'" class="normal">'
            . Mage::helper('core')->__('Use Parent Category Settings').'</label>';
        $html .= '<script type="text/javascript">toggleValueElements($(\''.$htmlId.'\'), $(\''.$htmlId.'\').parentNode);
        $$(\'.select[name="general[default_sort_by]"]\').invoke("observe", "change", function(event) {
	        $$(\'.select[name="general[available_sort_by][]"] option[value="\' + this.value  + \'"]\')[0].writeAttribute("selected", "select");
	    });
        </script>';

        return $html;
    }
}
