<?php
/**
 * Magmodules.eu - http://www.magmodules.eu - info@magmodules.eu
 * =============================================================
 * NOTICE OF LICENSE [Single domain license]
 * This source file is subject to the EULA that is
 * available through the world-wide-web at:
 * http://www.magmodules.eu/license-agreement/
 * =============================================================
 * @category    Magmodules
 * @package     Magmodules_Snippets
 * @author      Magmodules <info@magmodules.eu>
 * @copyright   Copyright (c) 2014 (http://www.magmodules.eu)
 * @license     http://www.magmodules.eu/license-agreement/  
 * =============================================================
 */
 
class Magmodules_Snippets_Block_Twitter extends Mage_Core_Block_Template {
	
    protected function _construct() {
        parent::_construct();	
	        $this->setTwitterEnabled(0);

		if(Mage::registry('product')) {
			if(Mage::getStoreConfig('snippets/twitter/product') && Mage::getStoreConfig('snippets/general/enabled')) {			
				 $this->setTwitterEnabled(1);
			} 
		} 

		if(Mage::registry('current_category') && !Mage::registry('product')) {
			if(Mage::getStoreConfig('snippets/twitter/category') && Mage::getStoreConfig('snippets/general/enabled')) {			
				 $this->setTwitterEnabled(1);
			} 
		} 
    }
	
    public function getTwitterUrl() {
        return $this->helper('snippets')->getTwitterUrl();
    }	

    public function getTwitterTitle() {
        return $this->helper('snippets')->getTwitterTitle();
    }	

    public function getTwitterDescription() {
        return $this->helper('snippets')->getDescription();
    }	
    
    public function getTwitterImageUrl() {
        return $this->helper('snippets')->getImageUrl();
    }	

    public function getTwitterPrice() {
        return $this->helper('snippets')->getCleanPrice();
    }	
    
    public function getTwitterAvailability() {
        return $this->helper('snippets')->getAvailability();
    }	
        
    public function getTwitterUser() {
        return Mage::getStoreConfig('snippets/twitter/username');
    }	
  
}