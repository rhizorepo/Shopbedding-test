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
class MageWorkshop_DetailedReview_Model_Mysql4_Sales_Order_Collection extends Mage_Sales_Model_Mysql4_Order_Collection
{
    protected $_salesFlatOrderItemTable;

    /**
     * @inherit
     */
    public function __construct()
    {
        parent::__construct();
        $this->_salesFlatOrderItemTable   = $this->getTable('sales/order_item');
    }

    /**
     * Add filter by specified product
     *
     * @param int $productId
     * @return $this
     */
    public function addProductFilter($productId)
    {
        $fromTables = $this->_select->getPart(Zend_Db_Select::FROM);

        if (!isset($fromTables[$this->_salesFlatOrderItemTable])) {
            $this->getSelect()->joinInner(
                array('soi' => $this->_salesFlatOrderItemTable),
                'main_table.entity_id = soi.order_id',
                array()
            );
        }

        $this->addFilter('product', $this->getConnection()->quoteInto('soi.product_id = ?', $productId), 'string');

        return $this;
    }
}
