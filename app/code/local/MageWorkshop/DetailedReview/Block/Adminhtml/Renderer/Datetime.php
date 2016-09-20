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
class MageWorkshop_DetailedReview_Block_Adminhtml_Renderer_Datetime extends Varien_Data_Form_Element_Date
{
    public function getValue($format = null)
    {
        if (empty($this->_value)) {
            return '';
        }
        if (null === $format) {
            $format = $this->getFormat();
        }
        try {
            $this->_value = Mage::app()->getLocale()->date($this->_value, Varien_Date::DATETIME_INTERNAL_FORMAT);
        }
        catch (Exception $e)
        {
            $this->_value = Mage::app()->getLocale()->date($this->_value, Varien_Date::DATETIME_INTERNAL_FORMAT);
        }
        return $this->_value->toString($format);
    }
}
