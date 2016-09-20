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
 * Class MageWorkshop_DetailedReview_Block_Adminhtml_Uninstall_Button
 *
 * @method setElement(Varien_Data_Form_Element_Abstract $element)
 */
class MageWorkshop_DetailedReview_Block_Adminhtml_Uninstall_Button extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    /**
     * @inherit
     */
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $this->setElement($element);
        /** @var MageWorkshop_DetailedReview_Helper_Data $helper */
        $helper = $this->helper('detailedreview');

        /** @var Mage_Adminhtml_Block_Widget_Button $buttonWidget */
        $buttonWidget = $this->getLayout()->createBlock('adminhtml/widget_button');
        $buttonWidget->setType('button')
            ->setClass('scalable')
            ->setLabel($helper->__('Uninstall'));

        if (Mage::getModel('drcore/uninstall')->checkPackageFile('DetailedReview')) {
            $url = $this->getUrl('adminhtml/mageworkshop_core_main/uninstall', array('package' => 'MageWorkshop_DetailedReview'));
            $text = $helper->__('This will completely uninstall Detailed Review extension and delete all related information. Reviews will get back to original (standard) state. Are you sure?');
            $buttonWidget->setOnClick("if(confirm('$text')){setLocation('$url');}");
        } else {
            $text = $helper->__('Cannot find package file for DetailedReview plugin.');
            $buttonWidget->setOnClick("alert('$text')");
        }
        return $buttonWidget->toHtml();
    }
}
