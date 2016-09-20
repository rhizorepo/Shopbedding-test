<?php
/**
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MIT License
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/mit-license.php
 *
 * @category    Hunter
 * @package     Hunter_Crawler
 * @copyright   Copyright (c) 2015
 * @license     http://opensource.org/licenses/mit-license.php MIT License
 * @author      Roman Tkachenko roman.tkachenko@huntersconsult.com
 */

class Hunter_Crawler_Model_Observer_Queue {
	
    const QUEUE_LOG_FILE = 'crawler/queue.log';
    const ERROR_LOG_FILE = 'crawler/error.log';
	
    protected $_resource;
    protected $_readConnection;
    protected $_writeConnection;
	
    /**
     * Create queue
     */
    public function createJobQueue() {
        $this->_resource 		= Mage::getSingleton('core/resource');
        $this->_readConnection 	= $this->_resource->getConnection('core_read');
        $this->_writeConnection = $this->_resource->getConnection('core_write');
		
		$select 				= '';
		$helper 				= Mage::helper('hunter_crawler');
		$pageCachePriorityList 	= Mage::helper('hunter_crawler')->pageCachePriorityList;
		
		$helper->log('Crawler is running', array(), 1, 'queue');
		
        try {
            foreach($pageCachePriorityList as $entityType) {
                $beginTime = time();
				
				$helper->log('Start queue job for %s', $entityType, 2, 'queue');
				
                switch($entityType) {
                    case Mage_Catalog_Model_Category::ENTITY:
                        $select = $this->_getCategoriesSelect();
                        break;
					
                    case Mage_Catalog_Model_Product::ENTITY:
                        $select = $this->_getProductsSelect();
                        break;
					
                    case 'cms_page':
                        $collection = Mage::getModel('cms/page')->getCollection()
                            ->addFieldToSelect('identifier', 'page_key');
                        $select = $this->_addAdditionalColumns($collection->getSelect(), $entityType);
                        break;
                }
				
                if(!$select || !($select instanceof Varien_Db_Select)) {
					$helper->log('Query to DB is empty', array(), 1, 'queue-error');
                    continue;
                }
				
                $crawlerQueueTable 	= $this->_resource->getTableName('hunter_crawler/crawler_queue');
                $insertQuery 		= $select->insertIgnoreFromSelect($crawlerQueueTable, array('page_key', 'date_add', 'entity_type'));
                $result 			= $this->_writeConnection->query($insertQuery);
				
                unset($result);
                unset($select);
				
                $totalTime = time() - $beginTime;
				
				$helper->log('End queue job in %ss', $totalTime, 3, 'queue');
            }
        } catch(Exception $e) {
			$helper->log($e->getMessage(), array(), 1, 'queue-error');
            return;
        }
    }
	
    /**
     * Retrieve select for categories.
     * Select categories from eav, flat and url rewrites tables.
     *
     * @return string
     *
     * @throws Mage_Core_Exception
     */
    protected function _getCategoriesSelect()
    {
        $entityType = Mage_Catalog_Model_Category::ENTITY;
        $attributeModel = Mage::getModel('eav/entity_attribute')->loadByCode(Mage_Catalog_Model_Category::ENTITY, 'url_path');
        $urlPathAttributeId = $attributeModel->getId();

        $attributeModel = Mage::getModel('eav/entity_attribute')->loadByCode(Mage_Catalog_Model_Category::ENTITY, 'is_active');
        $isActiveAttributeId = $attributeModel->getId();

        $entityModel = Mage::getModel('eav/entity_type')->load(Mage_Catalog_Model_Category::ENTITY, 'entity_type_code');
        $entityTypeId = $entityModel->getId();

        /** Select from EAV tables */
        $selectEav = $this->_readConnection->select();
        $selectEav->from(array('e' => $this->_resource->getTableName('catalog/category')), null);
        $selectEav->joinInner(
            array('at_url_path' => $this->_resource->getTableName('catalog_category_entity_varchar')),
            "(at_url_path.entity_id = e.entity_id)
                AND (at_url_path.attribute_id = $urlPathAttributeId)
                AND (at_url_path.store_id = 0)",
            array('page_key' => 'value'));
        $selectEav->joinInner(
            array('at_is_active' => $this->_resource->getTableName('catalog_category_entity_int')),
            "(at_is_active.entity_id = e.entity_id)
              AND (at_is_active.attribute_id = $isActiveAttributeId)
              AND (at_is_active.store_id = 0)",
            null);
        $selectEav->where('e.entity_type_id = ?', $entityTypeId);
        $selectEav->where('e.level > ?', 2);
        $selectEav->where('at_is_active.value = ?', 1);

        /** Select from flat table */
        $selectFlat = $this->_readConnection->select();
        $selectFlat->from($this->_resource->getTableName('catalog/category_flat') . '_store_1', array('page_key' => 'url_path'));
        $selectFlat->where('is_active = ?', 1);
        $selectFlat->where('url_path IS NOT NULL');

        /** Select from url_rewrites */
        $selectUrlRewrite = $this->_readConnection->select();
		
        $selectUrlRewrite->from(
			array('url_rewrite' => $this->_getUrlRewriteTable()),
			array('page_key' => 'request_path')
		);
		
        $selectUrlRewrite->joinInner(
            array('at_is_active' => $this->_resource->getTableName('catalog_category_entity_int')),
            "(at_is_active.entity_id = substring_index(target_path, '/', -1))
              AND (at_is_active.attribute_id = $isActiveAttributeId)
              AND (at_is_active.store_id = 0)",
            null);
        $selectUrlRewrite->where('at_is_active.value = ?', 1);
        $selectUrlRewrite->where('target_path LIKE ?', 'catalog/category/%');
        $selectUrlRewrite->group('request_path');

        /** Union select */
        $selectUnion = $this->_readConnection->select();
        $selectUnion->union(array($selectEav, $selectFlat, $selectUrlRewrite));

        return $this->_addAdditionalColumns($selectUnion, $entityType);
    }

    /**
     * Retrieve select for products.
     * Select products from eav, flat and url rewrites tables.
     *
     * @return string
     *
     * @throws Mage_Core_Exception
     */
    protected function _getProductsSelect() {
		$store_id 	= 1;
        $entityType = Mage_Catalog_Model_Product::ENTITY;
        $condition 	= $this->_writeConnection->quoteInto('=?', Mage_Catalog_Model_Product_Status::STATUS_ENABLED);
		
        $selectEav = $this->_readConnection->select();
        $selectEav->from(array('e' => $this->_resource->getTableName('catalog/product')), null);
        $this->_addAttributeToSelect($selectEav, 'status', 'e.entity_id', $store_id, $condition);
        $this->_addAttributeToSelect($selectEav, 'visibility', 'e.entity_id', $store_id, 'IN(2, 4)');
        $this->_addAttributeToSelect($selectEav, 'url_path', 'e.entity_id', $store_id, 'IS NOT NULL', 'page_key');
		
        $selectUrlRewrite = $this->_readConnection->select();
        $selectUrlRewrite->from(array('url_rewrite' => $this->_getUrlRewriteTable()), array('page_key' => 'request_path'));
        $this->_addAttributeToSelect($selectUrlRewrite, 'status', 'url_rewrite.product_id', $store_id, $condition);
        $this->_addAttributeToSelect($selectUrlRewrite, 'visibility', 'url_rewrite.product_id', $store_id, 'IN(2, 4)');
        $selectUrlRewrite->where('target_path LIKE ?', 'catalog/product/%');
        $selectUrlRewrite->group('request_path');
		
        /** Union select */
        $selectUnion = $this->_readConnection->select();
        $selectUnion->union(array($selectUrlRewrite, $selectEav));
		
        return $this->_addAdditionalColumns($selectUnion, $entityType);
    }

    /**
     * Retrieve Db Select with additional columns
     *
     * @param Varien_Db_Select $inputSelect
     * @param $entityType
     *
     * @return Varien_Db_Select
     */
    protected function _addAdditionalColumns(Varien_Db_Select $inputSelect, $entityType)
    {
        $select = $this->_readConnection->select();
        $select->from($inputSelect);
        $select->columns(
            array(
                'date_add' => new Zend_Db_Expr('"' . date("Y-m-d H:i:s", Mage::getModel('core/date')->timestamp(time())) . '"'),
                'entity_type' => new Zend_Db_Expr('"' . $entityType . '"'),
            )
        );

        return $select;
    }
	
    protected function _addAttributeToSelect($select, $attrCode, $entity, $store, $condition = null, $column = null) {
        $attribute 		= Mage::getModel('eav/entity_attribute')->loadByCode(Mage_Catalog_Model_Product::ENTITY, $attrCode);
		
		if(!$attribute || !$attribute->getAttributeId()) {
			return false;
		}
		
        $attributeId    = $attribute->getAttributeId();
        $attributeTable = $attribute->getBackend()->getTable();
		
        if($attribute->getIsGlobal() == Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL) {
            $alias = 'ta_' . $attrCode;
			
            $select->joinInner(
                array($alias => $attributeTable),
                "{$alias}.entity_id = {$entity} AND {$alias}.attribute_id = {$attributeId} AND {$alias}.store_id = 0",
                array()
            );
			
            $expression = new Zend_Db_Expr("{$alias}.value");
        } else {
            $dAlias = 'tad_' . $attrCode;
            $sAlias = 'tas_' . $attrCode;
			
            $select->joinInner(
                array($dAlias => $attributeTable),
                "{$dAlias}.entity_id = {$entity} AND {$dAlias}.attribute_id = {$attributeId} AND {$dAlias}.store_id = 0",
                array()
            );
			
            $select->joinLeft(
                array($sAlias => $attributeTable),
                "{$sAlias}.entity_id = {$entity} AND {$sAlias}.attribute_id = {$attributeId} AND {$sAlias}.store_id = {$store}",
                array()
            );
			
            $expression = $this->_readConnection->getCheckSql(
				$this->_readConnection->getIfNullSql("{$sAlias}.value", -1) . ' > 0', "{$sAlias}.value", "{$dAlias}.value"
			);
        }
		
		if(!is_null($column)) {
			$select->columns(array($column => $expression));
		}
		
        if(!is_null($condition)) {
            $select->where("{$expression} {$condition}");
        }
		
        return $expression;
    }
	
    protected function _getUrlRewriteTable() {
		$isVersionEE13 = ('true' == (string) Mage::getConfig()->getNode('modules/Enterprise_UrlRewrite/active'));
		
		if($isVersionEE13) {
			$table = Mage::getSingleton('core/resource')->getTableName('enterprise_urlrewrite/url_rewrite');
		} else {
			$table = Mage::getSingleton('core/resource')->getTableName('core/url_rewrite');
		}
		
		return $table;
    }
	
}
