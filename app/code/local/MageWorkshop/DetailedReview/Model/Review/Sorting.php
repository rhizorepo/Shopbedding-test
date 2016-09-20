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
class MageWorkshop_DetailedReview_Model_Review_Sorting
{
    protected $_options;

    protected $_availableOptions = null;
    protected $_currentSorting;
    protected $_queryVar = 'sort';

    /**
     * @param bool $isMultiSelect
     * @return array
     */
    public function toOptionArray($isMultiSelect = false)
    {
        if (!$this->_options) {
            $helper = Mage::helper('detailedreview');
            $this->_options = array(
                array('value'=>'date_desc',    'label'=> $helper->__('Date - Newest First')),
                array('value'=>'date_asc',     'label'=> $helper->__('Date - Latest First')),
                array('value'=>'rate_desc',    'label'=> $helper->__('Highest Rated')),
                array('value'=>'rate_asc',     'label'=> $helper->__('Lowest Rated')),
                array('value'=>'most_helpful', 'label'=> $helper->__('Most Helpful')),
                array('value'=>'ownership',    'label'=> $helper->__('Ownership'))
            );
        }
        
        $options = $this->_options;
        if (!$isMultiSelect) {
            array_unshift($options, array('value' => '', 'label' => Mage::helper('adminhtml')->__('-- Please Select --')));
        }

        return $options;
    }

    /**
     * @return array
     */
    public function getAvailableOptions()
    {
        if (!isset($this->_availableOptions)) {
            $this->_availableOptions = array();
            $availableSorting = explode(',', Mage::getStoreConfig('detailedreview/show_review_info_settings/allow_sorting_by'));
            foreach ($this->toOptionArray(false) as $sorting) {
                if (in_array($sorting['value'], $availableSorting)) {
                    $this->_availableOptions[$sorting['value']] = $sorting['label'];
                }
            }
        }
        return $this->_availableOptions;
    }

    /**
     * @return string
     */
    public function getCurrentSorting()
    {
        if (!isset($this->_currentSorting)) {
            $currentSorting = Mage::app()->getRequest()->getParam($this->_queryVar);
            $availableOptions = $this->getAvailableOptions();
            if (!isset($currentSorting) || !array_key_exists($currentSorting, $availableOptions)) {
                $defaultOrdering = Mage::getStoreConfig('detailedreview/list_options/ordering');
                if (!array_key_exists($defaultOrdering, $availableOptions)) {
                    $defaultOrdering = key($availableOptions);
                }
                $currentSorting = $defaultOrdering;
            }
            $this->_currentSorting = $currentSorting;
        }
        return $this->_currentSorting;
    }
}
