<?php

class GoMage_Sales_Model_Resource_Quote extends Mage_Sales_Model_Resource_Quote
{
    /**
     * Get reserved order id
     *
     * @param Mage_Sales_Model_Quote $quote
     * @return string
     */
    public function getReservedOrderId($quote)
    {
        $storeId = (int)$quote->getStoreId();
        return Mage::getSingleton('eav/config')->getEntityType(Mage_Sales_Model_Order::ENTITY)
            ->setIncrementModel('gomage_sales/increment_order')
            ->fetchNewIncrementId($storeId);
    }

    /**
     * Check is order increment id use in sales/order table
     *
     * @param int $orderIncrementId
     * @return boolean
     */
    public function isOrderIncrementIdUsed($orderIncrementId)
    {
        $adapter   = $this->_getReadAdapter();
        $bind      = array(':increment_id' => $orderIncrementId); // Need NOT INT
        $select    = $adapter->select();
        $select->from($this->getTable('sales/order'), 'entity_id')
            ->where('increment_id = :increment_id');
        $entity_id = $adapter->fetchOne($select, $bind);
        
        if ($entity_id > 0) {
            return true;
        }

        return false;
    }
}

