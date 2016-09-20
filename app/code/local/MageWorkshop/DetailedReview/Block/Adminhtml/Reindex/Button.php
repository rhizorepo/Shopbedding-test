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
 * Class MageWorkshop_DetailedReview_Block_Adminhtml_Reindex_Button
 *
 * @method setElement(Varien_Data_Form_Element_Abstract $element)
 */
class MageWorkshop_DetailedReview_Block_Adminhtml_Reindex_Button extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    /**
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $this->setElement($element);
        /** @var Mage_Adminhtml_Block_Widget_Button $buttonWidget */
        $buttonWidget = $this->getLayout()->createBlock('adminhtml/widget_button');
        $url = Mage::helper('adminhtml')->getUrl('adminhtml/mageworkshop_detailedreview_main/reindex');
        return $buttonWidget->setType('button')
            ->setClass('scalable')
            ->setLabel(Mage::helper('detailedreview')->__('Reindex Data'))
            ->setOnClick("setLocation('$url')")
            ->toHtml();
    }
}
