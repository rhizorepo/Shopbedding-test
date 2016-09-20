<?php
class Hunter_Crawler_Helper_Page extends Mage_Core_Helper_Abstract {
	
	public function flush($page) {
		$this->pushToQueue($page);
	}
	
	public function pushToQueue($page) {
		if(!$page || !$page->getId()) {
			return $this;
		}
		
		Mage::getModel('hunter_crawler/queue')
				->setId(null)
				->setPageKey($page->getIdentifier())
				->setDateAdd(Varien_Date::now())
				->setEntityType('cms_page')
				->setIsLocked(0)
				->save();
	}
	
}
