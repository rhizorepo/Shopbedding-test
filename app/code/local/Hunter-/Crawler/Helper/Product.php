<?php
class Hunter_Crawler_Helper_Product extends Mage_Core_Helper_Abstract {
	
	public function flush($product = null) {
		$this->reindex($product);
		$this->pushToQueue($product);
	}
	
	public function reindex($product) {
		if(!$product || !$product->getId()) {
			return $this;
		}
		
		/* Rebuild catalog product flat index */
		Mage::getSingleton('catalog/product_flat_indexer')->saveProduct($product->getId());
		
		/* Rebuild stock data for product */
		Mage::getResourceSingleton('cataloginventory/indexer_stock')->reindexProducts(array($product->getId() => $product->getId()));
		
		/* Rebuild catalog category products index */
		Mage::getResourceModel('catalog/category_indexer_product')->catalogProductSave(
			Mage::getModel('index/event')
							->setEntity($product->getResource()->getType())
							->setType(Mage_Index_Model_Event::TYPE_SAVE)
							->setDataObject($product)
							->setEntityPk($product->getId())
							->addNewData('category_ids', $product->getCategoryIds())
		);
		
		/* Rebuild catalog index for layered navigation and product list */
		Mage::getResourceModel('catalog/product_indexer_eav')->catalogProductSave(
			Mage::getModel('index/event')
							->setEntity($product->getResource()->getType())
							->setType(Mage_Index_Model_Event::TYPE_SAVE)
							->setDataObject($product)
							->setEntityPk($product->getId())
							->addNewData('reindex_eav', true)
		);
		
		/* Rebuild catalog price index */
		Mage::getResourceModel('catalog/product_indexer_price')->reindexProductIds($product->getId());
		
		/* Rebuild url rewrites */
		Mage::getSingleton('catalog/url')->refreshProductRewrite($product->getId());
		
		/* Rebuild catalog search index */
		Mage::getSingleton('catalogsearch/fulltext')->rebuildIndex(null, $product->getId());
	}
	
	public function pushToQueue($product) {
		if(!$product || !$product->getId()) {
			return $this;
		}
		
		$rewrites = Mage::getModel('core/url_rewrite')->getCollection();
		
		$rewrites->getSelect()
					->where('id_path = ?', "product/{$product->getId()}")
					->orWhere('id_path LIKE ?', "product/{$product->getId()}/%");
		
		try {
			if(count($rewrites)) {
				foreach($rewrites as $rewrite) {
					$model = Mage::getModel('hunter_crawler/queue');
					
					$model->load($rewrite->getRequestPath(), 'page_key');
					
					if($model->getId()) {
						continue;
					}
					
					$model->setId(null)
							->setPageKey($rewrite->getRequestPath())
							->setDateAdd(Varien_Date::now())
							->setEntityType(Mage_Catalog_Model_Product::ENTITY)
							->setIsLocked(0);
					
					$model->save();
				}
			}
		} catch(Exception $e) {
			Mage::helper('hunter_crawler')->log(print_r($e->getMessage(), true), array(), 1, 'product-import-error');
		}
		
	}
	
}
