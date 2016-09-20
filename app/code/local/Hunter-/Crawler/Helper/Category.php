<?php
class Hunter_Crawler_Helper_Category extends Mage_Core_Helper_Abstract {
	
	public function flush($category) {
		$this->reindex($category);
		$this->pushToQueue($category);
	}
	
	public function reindex($category) {
		if(!$category || !$category->getId()) {
			return $this;
		}
		
		Mage::getResourceSingleton('catalog/category_flat')->synchronize($category);
	}
	
	public function pushToQueue($category) {
		if(!$category || !$category->getId()) {
			return $this;
		}
		
		$rewrites = Mage::getModel('core/url_rewrite')->getCollection();
		
		$rewrites->getSelect()->where('id_path LIKE ?', "category/{$category->getId()}");
		
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
						->setEntityType(Mage_Catalog_Model_Category::ENTITY)
						->setIsLocked(0)
						->save();
				
				$model->save();
			}
		}
	}
	
}
