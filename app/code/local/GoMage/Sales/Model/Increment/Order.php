<?php

class GoMage_Sales_Model_Increment_Order extends Mage_Eav_Model_Entity_Increment_Abstract
{
    public function getNextId()
    {
        $prefix = $this->getPrefix();
        /** @var Mage_Core_Model_Resource $resource */
        $resource = Mage::getSingleton('core/resource');
        $connection = $resource->getConnection('core_read');
        $select = $connection->select()
            ->from(
                (array('o' => $resource->getTableName('sales/order'))),
                new Zend_Db_Expr('MAX(o.increment_id)')
            )
            ->where("increment_id LIKE '?'", new Zend_Db_Expr($prefix. '%'));
        $last = $connection->fetchOne($select);

        if (strpos($last, $prefix) === 0) {
            $last = (int)substr($last, strlen($prefix));
        } else {
            $last = (int)$last;
        }

        $next = $last+1;
        return $this->format($next);
    }
}