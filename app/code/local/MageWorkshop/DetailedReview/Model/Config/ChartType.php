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
class MageWorkshop_DetailedReview_Model_Config_ChartType
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
                array('value'=>'mage', 'label'=> $helper->__('Magento Chart')),
                array('value'=>'Line', 'label'=> $helper->__('Line Chart')),
                array('value'=>'Column', 'label'=> $helper->__('Column Chart')),
                array('value'=>'Area', 'label'=> $helper->__('Area Chart')),
            );
        }

        $options = $this->_options;
        if (!$isMultiSelect) {
            array_unshift($options, array(
                'value' => '',
                'label' => Mage::helper('adminhtml')->__('-- Please Select --')
            ));
        }

        return $options;
    }
}
