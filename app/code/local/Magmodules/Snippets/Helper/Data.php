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
 
class Magmodules_Snippets_Helper_Data extends Mage_Core_Helper_Abstract {

	public function getMarkup() 
	{
		if(Mage::registry('product')) {
			return Mage::getStoreConfig('snippets/products/type');
		} elseif(Mage::registry('current_category') && !Mage::registry('product')) {
			return Mage::getStoreConfig('snippets/category/type');		
		}			
	}	
	
	public function getContent() 
	{
		if(Mage::registry('current_product')) {
			$type = Mage::getStoreConfig('snippets/products/type');
			if($type == 'visible') {
				if(Mage::getStoreConfig('snippets/products/location') == 'advanced') {
					return Mage::getStoreConfig('snippets/products/location_custom');			
				} else {
					return Mage::getStoreConfig('snippets/products/location');		
				}
			}
			if($type == 'footer') {
				return Mage::getStoreConfig('snippets/products/location_ft');
			}
		} elseif(Mage::registry('current_category') && !Mage::registry('product')) {
			$type = Mage::getStoreConfig('snippets/category/type');
			if($type == 'visible') {
				return Mage::getStoreConfig('snippets/category/location');		
			}
			if($type == 'footer') {
				return Mage::getStoreConfig('snippets/category/location_ft');
			}
		}
	}	

	public function getPosition() 
	{
		if(Mage::registry('current_product')) {
			$type = Mage::getStoreConfig('snippets/products/type');
			if($type == 'visible') {
				return Mage::getStoreConfig('snippets/products/position');
			}	
			if($type == 'footer') {
				return Mage::getStoreConfig('snippets/products/position_ft');
			}	
		} elseif(Mage::registry('current_category') && !Mage::registry('product')) {
			$type = Mage::getStoreConfig('snippets/category/type');
			if($type == 'visible') {
				return Mage::getStoreConfig('snippets/category/position');
			}	
			if($type == 'footer') {
				return Mage::getStoreConfig('snippets/category/position_ft');
			}	
		}
	}	
	
	public function getSnippets() 
	{
		if((Mage::registry('current_product') && (Mage::getStoreConfig('snippets/products/enabled')))):
			$prodId = Mage::registry('current_product')->getId();
			$product = Mage::getModel('catalog/product')->load($prodId);            
			return $product;
		elseif((!Mage::registry('current_product') && (Mage::getStoreConfig('snippets/category/enabled')))):
			$catId = Mage::registry('current_category')->getId();
			$category = Mage::getModel('catalog/category')->load($catId);            
			return $category;
		else:
			return false;
		endif;
	}


	public function getPrice($prodId) 
	{	
		if($prodId < 1) {
			$prodId = Mage::registry('current_product')->getId();
		}		

		$product = Mage::getModel('catalog/product')->load($prodId);  
		$price = Mage::helper('tax')->getPrice($product, $product->getFinalPrice(), true);
		
		// GROUPED
		if($product->getTypeId() == 'grouped'):
			$_associatedProducts = $product->getTypeInstance(true)->getAssociatedProducts($product);
			$price = '';
			foreach ($_associatedProducts as $_item):
				$price_associated = Mage::helper('tax')->getPrice($_item, $_item->getFinalPrice(), true);
				if(($price_associated < $price) || ($price == '')):
					$price = $price_associated;
				endif;
			endforeach;		 
		endif; 
		
		// BUNDELD		
		if($product->getTypeId() == 'bundle'):
			$priceModel = $product->getPriceModel();
			$block = Mage::getSingleton('core/layout')->createBlock('bundle/catalog_product_view_type_bundle');
			$options = $block->setProduct($product)->getOptions();
			$price = 0;
			
			foreach ($options as $option) {
			  $selection = $option->getDefaultSelection();
			  if ($selection === null) { continue; }
				$prod_option = Mage::getModel('catalog/product')->load($selection->getProductId()); 
				$price += (Mage::helper('tax')->getPrice($prod_option, $prod_option->getFinalPrice(), true) * $selection->getSelectionQty()); 
			}				
			
			if($price < 0.01):
				$price = Mage::helper('tax')->getPrice($product, $product->getFinalPrice(), true);			
			endif;
		endif;
		return $price; 
	}

	public function getOffers() 
	{
		if(Mage::registry('current_product')) {		
			$price = $this->getPrice(0);
		}
		
		if(!Mage::registry('current_product')) {		
			$category 	= Mage::getModel('catalog/category')->load(Mage::registry('current_category')->getId());	
			$productIds = $category->getProductCollection()->addAttributeToSelect('*')->addAttributeToFilter('visibility', array('neq' => 1))->getAllIds();
			// Product Ratings
			$prices = array();			
			foreach($productIds as $productId){			
				$prices[] = $this->getPrice($productId);
			}			
			if($prices){
				$price = min($prices);		
			} else {
				$price = '0.00';
			}	
		}	
		
		$html = '<span itemprop="offers" itemscope itemtype="http://schema.org/Offer">';
		
		if($this->getMarkup() == 'hidden') {			
			$html .= '<meta itemprop="price" content="' . Mage::helper('core')->currency($price, true, false) . '" />';
		} else {
			if(!Mage::registry('current_product')) {
				$html .= Mage::helper('snippets')->__('Prices starting at:') . ' <span itemprop="price">' . Mage::helper('core')->currency($price, true, false) . '</span> ';
			} else {
				$prodId = Mage::registry('current_product')->getId();
				$product = Mage::getModel('catalog/product')->load($prodId);  

				if($product->getTypeId() == 'grouped') {
					$html .= Mage::helper('snippets')->__('Starting at:') . ' <span itemprop="price">' . Mage::helper('core')->currency($price, true, false) . '</span> ';
				} else {
					$html .= Mage::helper('snippets')->__('Price:') . ' <span itemprop="price">' . Mage::helper('core')->currency($price, true, false) . '</span> ';			
				}		
			}
		}

		$html .= '<meta itemprop="priceCurrency" content="' . Mage::app()->getStore()->getBaseCurrencyCode() . '" />';

		if(Mage::getStoreConfig('snippets/products/stock') && Mage::registry('current_product')) {
			$prodId = Mage::registry('current_product')->getId();
			$product = Mage::getModel('catalog/product')->load($prodId);  

			if($product->getIsInStock()) {
				$html .= '<link itemprop="availability" href="http://schema.org/InStock">';
				if($this->getMarkup() != 'hidden') {	
					$html .= ' - ' . Mage::helper('snippets')->__('In stock');
				}
			} else {
				$html .= '<link itemprop="availability" href="http://schema.org/OutOfStock">'; 
				if($this->getMarkup() != 'hidden') {	
					$html .= ' - ' . Mage::helper('snippets')->__('Out of stock');		
				}
			}
		}
		
		$html .= '</span>';
		return $html;	
	}

			
	public function getEnabled()
	{
		$enabled = Mage::getStoreConfig('snippets/general/enabled');
		$block = ''; $enabled_ent = '';	 $type = '';			
		
		if(Mage::registry('current_product')) {
			$enabled_ent 	= Mage::getStoreConfig('snippets/products/enabled');
			$type 			= Mage::getStoreConfig('snippets/products/type');
			if($type == 'visible') {
				$block 	= Mage::getStoreConfig('snippets/products/location');
			}
			if($type == 'footer') {
				$block 	= Mage::getStoreConfig('snippets/products/location_ft');
			}
		} elseif(Mage::registry('current_category')) {
			$enabled_ent 	= Mage::getStoreConfig('snippets/category/enabled');			
			$type 			= Mage::getStoreConfig('snippets/category/type');	
			if($type == 'visible') {
				$block 	= Mage::getStoreConfig('snippets/category/location');
			}
			if($type == 'footer') {
				$block 	= Mage::getStoreConfig('snippets/category/location_ft');
			}	
		}
				
		if(($block == '') || ($enabled == '') || ($enabled_ent == '') || ($type == 'hidden')) {
			return false;
		} else {
			return true;
		}
			
	}	

	public function getProductAvailability() 
	{	
		if(Mage::registry('current_product')) {				
			$prodId = Mage::registry('current_product')->getId();
			$product = Mage::getModel('catalog/product')->load($prodId);            		
			$stockhtml = '<link itemprop="availability" href="http://schema.org/OutOfStock">';
			if($product->getIsInStock()) {
				$stockhtml = '<link itemprop="availability" href="http://schema.org/InStock">';
			}			
		}			
		return $stockhtml;
	}
	
	public function getProductBrand() 
	{		
		if(Mage::getStoreConfig('snippets/products/brand')) {							
			$attribute 		= Mage::getStoreConfig('snippets/products/brand_attribute');		
			$prodId 		= Mage::registry('current_product')->getId();
			$product 		= Mage::getModel('catalog/product')->load($prodId);            
			$brand 			= $product->getAttributeText($attribute);

			if($brand) {
				return $brand;
			} else {
				return false;
			}	
		}	
	}

	public function getProductColor() 
	{		
		if(Mage::getStoreConfig('snippets/products/color')) {							
			$attribute 		= Mage::getStoreConfig('snippets/products/color_attribute');		
			$prodId 		= Mage::registry('current_product')->getId();
			$product 		= Mage::getModel('catalog/product')->load($prodId);            
			$color 			= $product->getAttributeText($attribute);

			if($color) {
				return $color;
			} else {
				return false;
			}	
		}	
	}

	public function getProductModel() 
	{		
		if(Mage::getStoreConfig('snippets/products/model')) {							
			$attribute 		= Mage::getStoreConfig('snippets/products/model_attribute');		
			$prodId 		= Mage::registry('current_product')->getId();
			$product 		= Mage::getModel('catalog/product')->load($prodId);            
			$model 			= $product->getAttributeText($attribute);

			if(!$model) {
				$model = trim(Mage::getResourceModel('catalog/product')->getAttributeRawValue($prodId, $attribute, 0));	
			}	

			if($model) {
				return $model;
			} else {
				return false;
			}	
		}	
	}	

	public function getProductEan() 
	{		
		if(Mage::getStoreConfig('snippets/products/ean')) {							
			$attribute 		= Mage::getStoreConfig('snippets/products/ean_attribute');		
			$prodId 		= Mage::registry('current_product')->getId();
			$product 		= Mage::getModel('catalog/product')->load($prodId);            
			$ean 			= trim(Mage::getResourceModel('catalog/product')->getAttributeRawValue($prodId, $attribute, 0));	
			$type 			= Mage::getStoreConfig('snippets/products/ean_type');		
			
			if($ean) {
				if($type == 'gtin8') {
					$value = str_pad($ean, 8, "0", STR_PAD_LEFT);		
				}
				if($type == 'gtin13') {
					$value = str_pad($ean, 13, "0", STR_PAD_LEFT);		
					$type_text = $type;				
				}
				if($type == 'gtin14') {
					$value = str_pad($ean, 14, "0", STR_PAD_LEFT);				
				}
				return $value;
			} else { 
				return false;			
			}			
		}	
	}	
		
	public function getDescription() 
	{	
		if(Mage::getStoreConfig('snippets/products/description') && Mage::registry('current_product')) {
			$prodId = Mage::registry('current_product')->getId();
			$attribute = Mage::getStoreConfig('snippets/products/description_attribute');
			
			if(!$attribute) {
				$product = Mage::getModel('catalog/product')->load($prodId);            
				$desciption = $product->getShortDescription();
			} else {
				$desciption = Mage::getResourceModel('catalog/product')->getAttributeRawValue($prodId, $attribute, Mage::app()->getStore()->getStoreId());
			}
		}
		if(Mage::getStoreConfig('snippets/category/description') && !Mage::registry('current_product')) {
			$catId = Mage::registry('current_category')->getId();			
			$category = Mage::getModel('catalog/category')->load($catId);        
			$desciption = $category->getDescription();
		}			
		if($desciption) {
			return strip_tags($desciption);
		} else {
			return false;
		}	
	}
	
	public function getReviewSnippets() 
	{
		$reviewsCount = '';

		// REVIEWS FOR PRODUCTS		
		if(Mage::getStoreConfig('snippets/products/reviews') && Mage::registry('current_product')) {		
			$name = Mage::registry('current_product')->getName(); 
			$prodId = Mage::registry('current_product')->getId();
			$product = Mage::getModel('catalog/product')->load($prodId);            
			$summaryData = Mage::getModel('review/review_summary')->setStoreId(Mage::app()->getStore()->getStoreId())->load($prodId);
			$reviewsCount = $summaryData->getReviewsCount();  
			$ratingPerc = $summaryData->getRatingSummary();
			if(Mage::getStoreConfig('snippets/products/reviews_metric') == '5') {		
				$ratingSummary = round(($summaryData->getRatingSummary() / 20), 1);
				$bestRating = '5';
			} else {
				$ratingSummary = round($summaryData->getRatingSummary());
				$bestRating = '100';		
			}
			if(Mage::getStoreConfig('snippets/products/reviews_type') == 'votes') {		
				$reviewstype = 'ratingCount';
			} else {
				$reviewstype = 'reviewCount';		
			}	
		}

		// REVIEWS FOR CATEGORIES
		if(Mage::getStoreConfig('snippets/category/reviews') && !Mage::registry('current_product')) {		
			$name = Mage::registry('current_category')->getName(); 
			// GET AL PRODUCTS
			$category 	= Mage::getModel('catalog/category')->load(Mage::registry('current_category')->getId());	
			$productIds = $category->getProductCollection()->addAttributeToSelect('*')->addAttributeToFilter('visibility', array('neq' => 1))->getAllIds();

			// Product Ratings
			$product_ratings = array();			
			foreach($productIds as $productId){			
				$product_ratings[] = Mage::getModel('review/review_summary')->setStoreId(Mage::app()->getStore()->getId())->load($productId);	
			}
		
			$totals = array(); $count = 0;

			foreach($product_ratings as $rating) {
				if(($rating['reviews_count'] > 0) && ($rating['rating_summary'] > 0)) {
					$totals[] = $rating['rating_summary'];	
					$count = ($count + $rating['reviews_count']);
				}
			}
			
			if(count($totals) > 0) {
				$ratingSummary = (array_sum($totals) / count($totals));
			} else {
				$ratingSummary = '';
			}	
	
			$ratingPerc = $ratingSummary;
			$reviewsCount = $count;
						
			if(Mage::getStoreConfig('snippets/category/reviews_metric') == '5') {		
				$ratingSummary = round(($ratingSummary / 20), 1);
				$bestRating = '5';
			} else {
				$ratingSummary = round($ratingSummary);
				$bestRating = '100';		
			}
			
			if(Mage::getStoreConfig('snippets/category/reviews_type') == 'votes') {		
				$reviewstype = 'ratingCount';
			} else {
				$reviewstype = 'reviewCount';		
			}	
		}

		// HTML DATA		
		if($reviewsCount > 0) {	
			$html = '';		
			if($this->getMarkup() == 'footer') {
				$html = Mage::helper('snippets')->__('Our %s has been rated %s based on %s individual customer review(s)', '<span itemprop="name">' . $name . '</span>', '<span itemprop="aggregateRating" itemscope itemtype="http://schema.org/AggregateRating"><span itemprop="ratingValue">' . $ratingSummary  . '</span>/<span itemprop="bestRating">' . $bestRating . '</span>', '<span itemprop="' . $reviewstype . '">' . $reviewsCount . '</span>');
			} elseif($this->getMarkup() == 'hidden') {
				$html = '<div itemprop="aggregateRating" itemscope itemtype="http://schema.org/AggregateRating">';
				$html .= '	<meta itemprop="ratingValue" content="' . $ratingSummary . '" />';
				$html .= '	<meta itemprop="' . $reviewstype . '" content="' . $reviewsCount . '" />';
				$html .= '	<meta itemprop="bestRating" content="' . $bestRating . '" />';				
				$html .= '</div>';		
			} else {					
				$html =  '<div itemprop="aggregateRating" itemscope itemtype="http://schema.org/AggregateRating">';
				$html .= '<div class="rating-box">';
				$html .= '	<div class="rating" style="width:' .  $ratingPerc . '%"></div>';
				$html .= '</div>';
				$html .= '<i>' . Mage::helper('snippets')->__('Rating: %s based on %s review(s)', '<span itemprop="ratingValue">' . $ratingSummary  . '</span>/<span itemprop="bestRating">' . $bestRating . '</span>','<span itemprop="' . $reviewstype . '">' . $reviewsCount . '</span>') . '</i>';
				$html .= '</div>';
			}		 
			return $html; 
		}			
	}
	
     	
	public function getExtraFields() 
	{

		if(Mage::registry('current_product')) {
			$fields = array();
			
			// Brand
			if($brand = $this->getProductBrand()): 
				$data = '<span itemprop="brand">' . $this->escapeHtml($this->getProductBrand()) . '</span>';
				$fields[] = array('value'=> $data, 'label'=> 'Brand', 'clean'=> $this->escapeHtml($this->getProductBrand()), 'itemprop'=> 'brand');			
			endif;

			// Color
			if($color = $this->getProductColor()): 
				$data = '<span itemprop="color">' . $this->escapeHtml($this->getProductColor()) . '</span>';
				$fields[] = array('value'=> $data, 'label'=> 'Color', 'clean'=> $this->escapeHtml($this->getProductColor()), 'itemprop'=> 'color');			
			endif;

			// Model
			if($model = $this->getProductModel()): 
				$data = '<span itemprop="model">' . $this->escapeHtml($this->getProductModel()) . '</span>';
				$fields[] = array('value'=> $data, 'label'=> 'Model', 'clean'=> $this->escapeHtml($this->getProductModel()), 'itemprop'=> 'model');			
			endif;		

			// EAN
			if($model = $this->getProductEan()): 
				$type = Mage::getStoreConfig('snippets/products/ean_type');	
				$data = '<span itemprop="' . $type. '">' . $this->escapeHtml($this->getProductEan()) . '</span>';
				$fields[] = array('value'=> $data, 'label'=> 'Product ID', 'clean'=> $this->escapeHtml($this->getProductEan()), 'itemprop'=> $type);			
			endif;	
							
			return $fields;
		}		
	}	
	
	public function getThumbnail() 
	{
		if(Mage::registry('current_product')) {	
			$prodId = Mage::registry('current_product')->getId();
			$product = Mage::getModel('catalog/product')->load($prodId);      
			return Mage::helper('catalog/image')->init($product, 'small_image')->resize(75);
		}
	}	

	public function getImageUrl() 
	{
		if(Mage::registry('current_product')) {	
			$prodId = Mage::registry('current_product')->getId();
			$product = Mage::getModel('catalog/product')->load($prodId);      
			return Mage::helper('catalog/image')->init($product, 'image');
		}
	}	
	    
    public function getTwitterUrl() 
    {
        return Mage::helper('core/url')->getCurrentUrl(); 
    }	

    public function getTwitterTitle() 
    {
		if(Mage::registry('current_category') && !Mage::registry('product')) {		
			$name = Mage::registry('current_category')->getName(); 
		} else {
			$name = Mage::registry('current_product')->getName(); 		
		}	
        return $name; 
   }	
    

	public function getTwitterSite() 
	{
        return Mage::helper('core/url')->getCurrentUrl(); 
   }	
	

    public function getTwitterCreator() 
    {
        return Mage::helper('core/url')->getCurrentUrl(); 
	}	

	public function getCleanPrice() 
	{
		if(Mage::registry('current_product')) {		
			$price = $this->getPrice(0);
		}
		
		if(!Mage::registry('current_product')) {		
			$category 	= Mage::getModel('catalog/category')->load(Mage::registry('current_category')->getId());	
			$productIds = $category->getProductCollection()->addAttributeToSelect('*')->addAttributeToFilter('visibility', array('neq' => 1))->getAllIds();
			// Product Ratings
			$prices = array();			
			foreach($productIds as $productId){			
				$prices[] = $this->getPrice($productId);
			}			
			$price = min($prices);		
		}	
		
		return Mage::helper('core')->currency($price, true, false);	
	}
		
		
	public function getAvailability()
	{
		if(Mage::getStoreConfig('snippets/products/stock') && Mage::registry('current_product')) {
			$prodId = Mage::registry('current_product')->getId();
			$product = Mage::getModel('catalog/product')->load($prodId);  

			if($product->getIsInStock()) {
				$avail = Mage::helper('snippets')->__('In stock');
			} else {
				$avail = Mage::helper('snippets')->__('Out of stock');		
			}	
		} else {
			$avail = Mage::helper('snippets')->__('In stock');		
		}
		return $avail;
	}	
}