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
class MageWorkshop_DetailedReview_Model_Mysql4_Purchase extends Mage_Core_Model_Mysql4_Abstract
{
    /**
     * @inherit
     */
    protected function _construct()
    {
        $this->_init('detailedreview/purchase', 'item_id');
    }

    public function loadByAttributes($attributes)
    {
        $adapter = $this->_getReadAdapter();
        $where   = array();
        foreach ($attributes as $attributeCode => $value) {
            $where[] = sprintf('%s=:%s', $attributeCode, $attributeCode);
        }
        $select = $adapter->select()
            ->from($this->getMainTable())
            ->where(implode(' AND ', $where));

        $binds = $attributes;

        return $adapter->fetchRow($select, $binds);
    }

    public function updateData($id = null)
    {
        $write  = $this->_getWriteAdapter();
        // read and prepare original order information
        $select = $write->select()
            ->distinct(true)
             ->from(
                 array('so' => $this->getTable('sales/order')),
                 array('customer_email','created_at', 'store_id')
             )
             ->join(
                 array('soi' => $this->getTable('sales/order_item')),
                 'so.entity_id = soi.order_id',
                 array('product_id')
             )
            ->join( array('p' => $this->getTable('catalog/product')),
                'p.entity_id = soi.product_id',
                null
            );

        if ($id) {
            $select->where('so.entity_id = ?', $id);
        }

        $insertSelect = $write->insertFromSelect(
            $select,
            $this->getMainTable(),
            array('customer_email', 'created_at', 'store_id', 'product_id'),
            Varien_Db_Adapter_Interface::INSERT_ON_DUPLICATE
        );

        $write->query($insertSelect);
    }
}
