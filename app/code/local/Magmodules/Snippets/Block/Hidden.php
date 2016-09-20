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
 
class Magmodules_Snippets_Block_Hidden extends Mage_Core_Block_Template {
	
    protected function _construct() {
        parent::_construct();	

		// Set enabled
		if(Mage::getStoreConfig('snippets/general/enabled')) {
	        $this->setSnippetsEnabled(1);
    	} else {
	        $this->setSnippetsEnabled(0);
	    }        	

		// Set template
		if(Mage::registry('product')) {
			if(Mage::getStoreConfig('snippets/products/type') == 'hidden') {
				$this->setTemplate('magmodules/snippets/product/hidden.phtml');	    			
			}
		} 

		if(Mage::registry('current_category') && !Mage::registry('product')) {
			if(Mage::getStoreConfig('snippets/category/type') == 'hidden') {
				$this->setTemplate('magmodules/snippets/product/hidden.phtml');	    			
			}
		} 
		
    }
	
	// Product & Markup
    public function getSnippets() {
        return $this->helper('snippets')->getSnippets();
    }	

    public function getMarkup() {
        return $this->helper('snippets')->getMarkup();
    }
        
    // Reviews	
    public function getReviews() {
        return $this->helper('snippets')->getReviewSnippets();
    }	

    public function getReviewSnippets() {
        return $this->helper('snippets')->getReviewSnippets();
    }	

    public function getReviewQty() {
        return $this->helper('snippets')->getReviewQty();
    }	

	// Price & Offers    
    public function getPrice() {
        return $this->helper('snippets')->getPrice();
    }	

    public function getPriceHtml() {
        return $this->helper('snippets')->getPriceHtml();
    }	

    public function getOffers() {
        return $this->helper('snippets')->getOffers();
    }	    

	// Extra Fields    
    public function getExtraFields() {
        return $this->helper('snippets')->getExtraFields();
    }	

	// Attributes
    public function getProductBrand() {
        return $this->helper('snippets')->getProductBrand();
    }	
    
    public function getProductColor() {
        return $this->helper('snippets')->getProductColor();
    }	

    public function getProductModel() {
        return $this->helper('snippets')->getProductModel();
    }	

    public function getProductEan() {
        return $this->helper('snippets')->getProductEan();
    }	
            
    public function getDescription() {
        return $this->helper('snippets')->getDescription();
    }	
    
    public function getProductAvailability() {
        return $this->helper('snippets')->getProductAvailability();
    }	
    
    public function getThumbnail() {
        return $this->helper('snippets')->getThumbnail();
    }	
    
}