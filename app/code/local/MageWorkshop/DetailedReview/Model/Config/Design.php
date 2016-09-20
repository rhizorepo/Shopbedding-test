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
class MageWorkshop_DetailedReview_Model_Config_Design
{
    /** @var array $_options */
    protected $_options = array();

    /**
     * @param bool $isMultiSelect
     * @return array
     */
    public function toOptionArray($isMultiSelect = false)
    {
        if (empty($this->_options)) {
            $helper = Mage::helper('detailedreview');
            $this->_options = array(
                array('value'=>'base', 'label'=> $helper->__('Default Magento Theme')),
                array('value'=>'rwd', 'label'=> $helper->__('Responsive Magento Theme'))
            );
        }
        $options = $this->_options;
        return $options;
    }
}
