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
class MageWorkshop_DetailedReview_Model_Mysql4_Review_Proscons_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    protected $_entityType;
    protected $_entityName;
    protected $_className;

    /**
     * @inherit
     */
    protected function _construct()
    {
        $this->_init('detailedreview/review_proscons');
    }

    /**
     * @return $this
     */
    public function addStoreData()
    {
        /** @var MageWorkshop_DetailedReview_Model_Review_Proscons $item */
        foreach ($this as $item) {
            $item->getStoreIds();
        }
        return $this;
    }

    /**
     * Add store filter to collection
     *
     * @param array $storeId
     * @return Varien_Data_Collection_Db
     */
    public function addStoreFilter($storeId = array())
    {
        if (!Mage::app()->isSingleStoreMode()) {
            if(empty($storeId)) {
                $storeId = Mage::app()->getStore()->getId();
            }
            $storeTable = $this->getResource()->getTable('detailedreview/review_proscons_store');
            $this->getSelect()
                 ->join(array('store' => $storeTable), 'main_table.entity_id=store.entity_id', array())
                 ->where('store.store_id IN (?)', (array) $storeId)
                 ->where('store.entity_type = ?', $this->_entityType);
        }
        return $this;
    }

    /**
     * Method was created for filter by store to work properly in admin panel
     *
     * @param string $field
     * @param null $condition
     * @return $this|Mage_Eav_Model_Entity_Collection_Abstract
     */
    public function addFieldToFilter($field, $condition = null)
    {
        switch ($field) {
            case 'store_ids':
                if (isset($condition['eq'])) {
                    $this->addStoreFilter($condition['eq']);
                } else {
                    $this->addStoreFilter();
                }
                break;
            default:
                parent::addFieldToFilter($field, $condition);
        }
        return $this;
    }

    /**
     * @param string $type
     * @return $this
     */
    public function setType($type)
    {
        $this->_entityType = $type;
        $this->_entityName = MageWorkshop_DetailedReview_Model_Source_EntityType::getEntityNameByType($this->_entityType);
        $this->_className = MageWorkshop_DetailedReview_Model_Source_EntityType::getClassNameByType($this->_entityType);
        $this->addFieldToFilter('main_table.entity_type', array('eq' => $type));
        return $this;
    }

    /**
     * @return $this
     */
    public function addUserFilter()
    {
        $this->addFieldToFilter('wrote_by', array('eq' => 0));
        return $this;
    }
}
