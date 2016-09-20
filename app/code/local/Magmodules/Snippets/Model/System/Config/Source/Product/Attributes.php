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
 * @copyright   Copyright (c) 2013 (http://www.magmodules.eu)
 * @license     http://www.magmodules.eu/license-agreement/  
 * =============================================================
 */
 
class Magmodules_Snippets_Model_System_Config_Source_Product_Attributes
{
    public function toOptionArray(){
        $options = array();
        $entityTypeId = Mage::getModel('eav/entity_type')->loadByCode('catalog_product')->getEntityTypeId();
        $attributes = Mage::getModel('eav/entity_attribute')->getCollection()->addFilter('entity_type_id', $entityTypeId)->setOrder('attribute_code', 'ASC');
        foreach ($attributes as $attribute){
            $item = array();
            $item['value'] = $attribute->getAttributeCode();
            if ($attribute->getFrontendLabel()){
                $item['label'] = $attribute->getFrontendLabel();
            }
            else{
                $item['label'] = $attribute->getAttributeCode();
            }
            $options[] = $item;
        }
       
        return $options;
    }
}
