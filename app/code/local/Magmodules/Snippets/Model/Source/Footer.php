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
 
class Magmodules_Snippets_Model_Source_Footer
{
	public function toOptionArray()
	{
		$location = array();
		$location[] = array('value'=>'Mage_Page_Block_Html_Footer', 'label'=> Mage::helper('snippets')->__('Footer'));
		$location[] = array('value'=>'', 'label'=> Mage::helper('snippets')->__('-- Manual'));				
		return $location;
	}
}