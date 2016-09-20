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
class MageWorkshop_DetailedReview_Model_Mysql4_Review_Proscons extends Mage_Core_Model_Mysql4_Abstract
{
    protected $_entityType;
    protected $_entityName;
    protected $_className;

    /**
     * @inherit
     */
    protected function _construct()
    {
        $this->_entityName = MageWorkshop_DetailedReview_Model_Source_EntityType::getEntityNameByType($this->_entityType);
        $this->_className = MageWorkshop_DetailedReview_Model_Source_EntityType::getClassNameByType($this->_entityType);
        $this->_init('detailedreview/review_proscons', 'entity_id');
    }

    /**
     * @param MageWorkshop_DetailedReview_Model_Review_Proscons $prosCons
     */
    public function loadStoreIds($prosCons)
    {
        $storeIds = array();
        if ($prosCons->getId()) {
            $storeIds = $this->lookupStoreIds($prosCons->getId(), $prosCons->getEntityType());
        }
        $prosCons->setStoreIds($storeIds);
    }

    /**
     * Get store ids to which specified item is assigned
     *
     * @param int $id
     * @param string $entityType
     * @return array
     */
    public function lookupStoreIds($id, $entityType)
    {
        return $this->_getReadAdapter()->fetchCol(
            $this->_getReadAdapter()
                 ->select()
                 ->from($this->getTable('detailedreview/review_proscons_store'), 'store_id')
                 ->where('entity_id = ?', $id)
                 ->where('entity_type = ?', $entityType)
        );
    }

    /**
     * @param Mage_Core_Model_Abstract $prosCons
     * @return $this
     */
    public function _afterSave(Mage_Core_Model_Abstract $prosCons)
    {
        /** @var MageWorkshop_DetailedReview_Model_Review_Proscons $posCons */
        if ($storeIds = (array) $prosCons->getStoreIds()) {
            $deleteWhere = array();
            $deleteWhere[] = $this->_getWriteAdapter()->quoteInto('entity_id = ?', $prosCons->getEntityId());
            $deleteWhere[] = $this->_getWriteAdapter()->quoteInto('entity_type = ?', $prosCons->getEntityType(), 'string');

            $this->_getWriteAdapter()->delete($this->getTable('detailedreview/review_proscons_store'), $deleteWhere);

            foreach ($storeIds as $storeId) {
                $this->_addStore($prosCons, $storeId);
            }
        }
        return $this;
    }

    /**
     * Insert store info for the object
     *
     * @param MageWorkshop_DetailedReview_Model_Review_Proscons $prosCons
     * @param int $storeId
     */
    protected function _addStore(MageWorkshop_DetailedReview_Model_Review_Proscons $prosCons, $storeId)
    {
        $pollStoreData = array(
            'entity_id'   => $prosCons->getEntityId(),
            'entity_type' => $prosCons->getEntityType(),
            'store_id'    => $storeId
        );
        $this->_getWriteAdapter()->insert($this->getTable('detailedreview/review_proscons_store'), $pollStoreData);
    }

    /**
     * Perform actions after object delete
     *
     * @param Mage_Core_Model_Abstract $prosCons
     * @return $this
     */
    protected function _afterDelete(Mage_Core_Model_Abstract $prosCons)
    {
        /** @var MageWorkshop_DetailedReview_Model_Review_Proscons $posCons */
        $this->_getWriteAdapter()
             ->delete($this->getTable('detailedreview/review_proscons_store'), array(
                'entity_id = ?' => $prosCons->getEntityId(),
                'entity_type = ?' => $prosCons->getEntityType()
             ));
        return parent::_afterDelete($prosCons);
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
        return $this;
    }
}
