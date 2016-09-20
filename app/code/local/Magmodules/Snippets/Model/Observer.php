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
 
class Magmodules_Snippets_Model_Observer {

	public function setSnippetsData(Varien_Event_Observer $observer) {			
		if(Mage::app()->getFrontController()->getRequest()->getRouteName() == 'catalog') {
			$helper			= Mage::helper('snippets');	
			$position 		= $helper->getPosition(); 
			$enabled 		= $helper->getEnabled(); 	
			$markup 		= $helper->getMarkup(); 			
		
			if($enabled) {				
				$block			= $observer->getBlock();
				$fileName		= $block->getTemplateFile();
				$thisClass		= get_class($block);			
				$content 		= $helper->getContent(); 
				$normalOutput 	= $observer->getTransport()->getHtml();
				$argBefore		= null;
				$argAfter		= null;
			
				if($content == $thisClass) {								
					if($markup == 'footer') {
						$snipblock = $block->getLayout()->createBlock('snippets/products')->setTemplate('magmodules/snippets/product/footer.phtml')->toHtml();    		
					}
					if($markup == 'visible') {
						$snipblock = $block->getLayout()->createBlock('snippets/products')->setTemplate('magmodules/snippets/product/schema.phtml')->toHtml();    		
					}
					if($position == 'after') {
						$argAfter = $snipblock; 
					} else {
						$argBefore = $snipblock; 
					}			
				}
			$observer->getTransport()->setHtml($argBefore . $normalOutput . $argAfter);		
			}
		}
	}
}