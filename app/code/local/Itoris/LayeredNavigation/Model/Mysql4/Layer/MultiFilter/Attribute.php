<?php 
/**
 * ITORIS
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the ITORIS's Magento Extensions License Agreement
 * which is available through the world-wide-web at this URL:
 * http://www.itoris.com/magento-extensions-license.html
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to sales@itoris.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade the extensions to newer
 * versions in the future. If you wish to customize the extension for your
 * needs please refer to the license agreement or contact sales@itoris.com for more information.
 *
 * @category   ITORIS
 * @package    ITORIS_LAYEREDNAVIGATION
 * @copyright  Copyright (c) 2012 ITORIS INC. (http://www.itoris.com)
 * @license    http://www.itoris.com/magento-extensions-license.html  Commercial License
 */

/**
 * Resource model for attribute filter.
 */
class Itoris_LayeredNavigation_Model_Mysql4_Layer_MultiFilter_Attribute
			extends Mage_Catalog_Model_Resource_Eav_Mysql4_Layer_Filter_Attribute {

	/**
	 * Add condition to the collection.
	 *
	 * @param $filter
	 * @param $value
	 * @return Itoris_LayeredNavigation_Model_Mysql4_Layer_MultiFilter_Attribute
	 */
	public function applyFilterToCollection($filter, $value) {
		$collection = $filter->getLayer()->getProductCollection();
		$attribute  = $filter->getAttributeModel();
		$connection = $this->_getReadAdapter();
		$tableAlias = $attribute->getAttributeCode() . '_idx';
		$conditions = array(
			"{$tableAlias}.entity_id = e.entity_id",
			$connection->quoteInto("{$tableAlias}.attribute_id = ?", $attribute->getAttributeId()),
			$connection->quoteInto("{$tableAlias}.store_id = ?", $collection->getStoreId()),
			$connection->quoteInto("{$tableAlias}.value in (?)", $value)
		);
		$collection->getSelect()->distinct();
		$collection->getSelect()->join(
			array($tableAlias => $this->getMainTable()),
			implode(' AND ', $conditions),
			array()
		);

		return $this;
	}


	/**
	 * Get attribute items count in the collection.
	 *
	 * @param $filter
	 * @return array
	 */
	public function getCount($filter) {
		/** @var $select Varien_Db_Select */
		$select = clone $filter->getLayer()->getProductCollection()->getSelect();
		// reset columns, order and limitation conditions
		$select->reset(Zend_Db_Select::COLUMNS);
		$select->reset(Zend_Db_Select::ORDER);
		$select->reset(Zend_Db_Select::LIMIT_COUNT);
		$select->reset(Zend_Db_Select::LIMIT_OFFSET);

		$connection = $this->_getReadAdapter();
		$attribute  = $filter->getAttributeModel();
		$tableAlias = sprintf('%s_idx', $attribute->getAttributeCode());

		$from = $select->getPart(Zend_Db_Select::FROM);
		unset($from[$tableAlias]);
		$select->setPart(Zend_Db_Select::FROM, $from);

		$conditions = array(
			"{$tableAlias}.entity_id = e.entity_id",
			$connection->quoteInto("{$tableAlias}.attribute_id = ?", $attribute->getAttributeId()),
			$connection->quoteInto("{$tableAlias}.store_id = ?", $filter->getStoreId()),
		);

		$select
			->join(
				array($tableAlias => $this->getMainTable()),
				join(' AND ', $conditions),
				array('value', 'count' => new Zend_Db_Expr("COUNT(distinct {$tableAlias}.entity_id)")))
			->group("{$tableAlias}.value");

		return $connection->fetchPairs($select);
	}


}
?>