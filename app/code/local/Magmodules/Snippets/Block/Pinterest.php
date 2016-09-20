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
 
class Magmodules_Snippets_Block_Pinterest extends Mage_Core_Block_Template {
	
    protected function _construct() {
        parent::_construct();	
	        $this->setPinterestEnabled(0);

		if(Mage::registry('product')) {
			if(Mage::getStoreConfig('snippets/pinterest/product') && Mage::getStoreConfig('snippets/general/enabled')) {			
				 $this->setPinterestEnabled(1);
			} 
		} 

		if(Mage::registry('current_category') && !Mage::registry('product')) {
			if(Mage::getStoreConfig('snippets/pinterest/category') && Mage::getStoreConfig('snippets/general/enabled')) {			
				 $this->setPinterestEnabled(1);
			} 
		} 
    }
	
    public function getSiteName() {
        if($sitename = Mage::getStoreConfig('snippets/pinterest/sitename')) {
	        return $sitename;
	    }    
    }	
  
}