<?php
error_reporting(E_ALL);
ini_set("display_errors", 1); 
ini_set('max_execution_time', 30000);

class Mage_Catalog_Model_Convert_Parser_Productforexp
    extends Mage_Catalog_Model_Convert_Parser_Product
{
    
    /**
     * Unparse (prepare data) loaded products
     *
     * @return Mage_Catalog_Model_Convert_Parser_Product
     */
    public function unparse()
    {
        $entityIds = $this->getData();
		//Mage::log($entityIds, null, 'custom.log');
		
        foreach ($entityIds as $i => $entityId) {
            $product = $this->getProductModel()
                ->setStoreId($this->getStoreId())
                ->load($entityId);
				
			$simpleProductId = $entityId;
			$parentIds = Mage::getResourceSingleton('catalog/product_type_configurable')
									->getParentIdsByChild($simpleProductId);
			$product1 = Mage::getModel('catalog/product')->load($parentIds[0]);
			if($product -> getTypeId() =="simple"){
				if($parentIds){
					//Mage::log($product1 -> getStatus(), null, 'custom.log');
					if($product1 -> getStatus() !=1){	
						continue;
					}
				}
			}
			
            $this->setProductTypeInstance($product);
            /* @var $product Mage_Catalog_Model_Product */

            $position = Mage::helper('catalog')->__('Line %d, SKU: %s', ($i+1), $product->getSku());
            $this->setPosition($position);
			$product_url = "";
			
				
				if($parentIds){
					$product1 = Mage::getModel('catalog/product')->load($parentIds[0]);
					$product_url = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB). $product1->getUrlKey() .".html";
					
				}
				else{
					$product_url = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB). $product->getUrlKey().".html";
					
				}
		

            $row = array(
                'store'         => $this->getStore()->getCode(),
                'websites'      => '',
                'attribute_set' => $this->getAttributeSetName($product->getEntityTypeId(),
                                        $product->getAttributeSetId()),
                'type'          => $product->getTypeId(),
                'category_ids'  => join(',', $product->getCategoryIds()),
				'url'			=> $product_url ,
				'url_key_custom'		=> $product->getUrlKey() . "-" . $entityId 
            );

            if ($this->getStore()->getCode() == Mage_Core_Model_Store::ADMIN_CODE) {
                $websiteCodes = array();
                foreach ($product->getWebsiteIds() as $websiteId) {
                    $websiteCode = Mage::app()->getWebsite($websiteId)->getCode();
                    $websiteCodes[$websiteCode] = $websiteCode;
                }
                $row['websites'] = join(',', $websiteCodes);
            } else {
                $row['websites'] = $this->getStore()->getWebsite()->getCode();
                if ($this->getVar('url_field')) {
                    $row['url'] = $product->getProductUrl(false);
                }
            }

            foreach ($product->getData() as $field => $value) {
                if (in_array($field, $this->_systemFields) || is_object($value)) {
                    continue;
                }

                $attribute = $this->getAttribute($field);
                if (!$attribute) {
                    continue;
                }

                if ($attribute->usesSource()) {
                    $option = $attribute->getSource()->getOptionText($value);
                    if ($value && empty($option) && $option != '0') {
                        $this->addException(
                            Mage::helper('catalog')->__('Invalid option ID specified for %s (%s), skipping the record.', $field, $value),
                            Mage_Dataflow_Model_Convert_Exception::ERROR
                        );
                        continue;
                    }
                    if (is_array($option)) {
                        $value = join(self::MULTI_DELIMITER, $option);
                    } else {
                        $value = $option;
                    }
                    unset($option);
                } elseif (is_array($value)) {
                    continue;
                }

                $row[$field] = $value;
            }

            if ($stockItem = $product->getStockItem()) {
                foreach ($stockItem->getData() as $field => $value) {
                    if (in_array($field, $this->_systemFields) || is_object($value)) {
                        continue;
                    }
                    $row[$field] = $value;
                }
            }

            foreach ($this->_imageFields as $field) {
                if (isset($row[$field]) && $row[$field] == 'no_selection') {
                    $row[$field] = null;
                }
            }

            $batchExport = $this->getBatchExportModel()
                ->setId(null)
                ->setBatchId($this->getBatchModel()->getId())
                ->setBatchData($row)
                ->setStatus(1)
                ->save();
            $product->reset();
        }

        return $this;
    }
	
	public function getFullUrl ($product){

		if ($category == null) {
			if( is_null($product->getCategoryIds() )){
				return $product->getProductUrl();
			}
			$catCount = 0;
			$productCategories = $product->getCategoryIds();
			// Go through all product's categories
			while( $catCount < count($productCategories) && $category == null ) {
				$tmpCategory = Mage::getModel('catalog/category')->load($productCategories[$catCount]);
				// See if category fits (active, url key, included in menu)
				
					$category = Mage::getModel('catalog/category')->load($productCategories[$catCount]);
				
			}
		}
		$url = (!is_null( $product->getUrlPath($category))) ?  Mage::getBaseUrl() . $product->getUrlPath($category) : $product->getProductUrl();
		return $url;
	}

}
