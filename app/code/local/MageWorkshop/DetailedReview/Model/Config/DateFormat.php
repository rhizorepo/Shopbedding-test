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
class MageWorkshop_DetailedReview_Model_Config_DateFormat
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
                array('value'=>'DD/MM/YYYY', 'label'=> $helper->__('DD/MM/YYYY')),
                array('value'=>'DD-MM-YYYY', 'label'=> $helper->__('DD-MM-YYYY')),
                array('value'=>'DD MM YYYY', 'label'=> $helper->__('DD MM YYYY')),
                array('value'=>'MM/DD/YYYY', 'label'=> $helper->__('MM/DD/YYYY')),
                array('value'=>'MM-DD-YYYY', 'label'=> $helper->__('MM-DD-YYYY')),
                array('value'=>'MM DD YYYY', 'label'=> $helper->__('MM DD YYYY')),
                array('value'=>'YYYY-MM-DD',  'label'=> $helper->__('YYYY-MM-DD')),
                array('value'=>'MMM DD YYYY', 'label'=> $helper->__('MMM DD YYYY')),
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
