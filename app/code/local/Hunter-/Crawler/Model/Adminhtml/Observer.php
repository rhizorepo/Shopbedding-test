<?php
class Hunter_Crawler_Model_Adminhtml_Observer {
	
    public function cmsPageSaveAfter(Varien_Event_Observer $observer) {
		$page = $observer->getEvent()->getDataObject();
		
		if(!$page || !$page->getId()) {
			return $this;
		}
		
        try {
			Mage::helper('hunter_crawler/page')->flush($page);
        } catch(Exception $e) {
			Mage::helper('hunter_crawler')->log($e->getMessage(), array(), 1, 'page-error');
        }
    }
	
    public function catalogCategorySaveAfter(Varien_Event_Observer $observer) {
		$category = $observer->getEvent()->getCategory();
		
		if(!$category || !$category->getId()) {
			return $this;
		}
		
        try {
			Mage::helper('hunter_crawler/category')->flush($category);
        } catch(Exception $e) {
			Mage::helper('hunter_crawler')->log($e->getMessage(), array(), 1, 'category-error');
        }
    }
	
    public function catalogProductSaveAfter(Varien_Event_Observer $observer) {
		$product = $observer->getEvent()->getProduct();
		
		if(!$product || !$product->getId()) {
			return $this;
		}
		
        try {
			Mage::helper('hunter_crawler/product')->flush($product);
        } catch(Exception $e) {
			Mage::helper('hunter_crawler')->log($e->getMessage(), array(), 1, 'product-error');
        }
    }
	
}
