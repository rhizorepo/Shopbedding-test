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
class MageWorkshop_DetailedReview_Block_Adminhtml_Version extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    /**
     * @inherit
     */
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        return (string) Mage::helper('detailedreview')->getExtensionVersion();
    }
}
