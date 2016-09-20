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
class MageWorkshop_DetailedReview_Model_Config_TimeFormat
{
    /** @var array $_options */
    protected $_options = array();

    /**
     * @param bool $isMultiSelect
     * @return array
     */
    public function toOptionArray($isMultiSelect = false)
    {
        if (!$this->_options) {
            $helper = Mage::helper('detailedreview');
            $this->_options = array(
                array('value'=>'HH:mm',        'label'=> $helper->__('HH:mm')),
                array('value'=>'HH:mm:ss',     'label'=> $helper->__('HH:mm:ss')),
                array('value'=>'hh:mm a',      'label'=> $helper->__('hh:mm a')),
                array('value'=>'hh:mm:ss a',   'label'=> $helper->__('hh:mm:ss a'))
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
