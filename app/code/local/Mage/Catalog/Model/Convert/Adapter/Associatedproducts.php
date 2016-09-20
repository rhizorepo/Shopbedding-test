<?php

/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category   Mage
 * @package    Mage_Catalog
 * @copyright  Copyright (c) 2008 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Mage_Catalog_Model_Convert_Adapter_Associatedproducts extends Mage_Catalog_Model_Convert_Adapter_Product {

    protected $_imageLabels = array();

    /**
     * Save product (import)
     *
     * @param array $importData
     * @throws Mage_Core_Exception
     * @return bool
     */
    public function saveRow(array $importData) {
        $removeAllImages = false;
        $updateImages = true;

        $this->_imageFields[] = "image_1";
        $this->_imageFields[] = "image_2";
        $this->_imageFields[] = "image_3";
        $this->_imageFields[] = "image_4";
        $this->_imageFields[] = "image_5";

        $product = $this->getProductModel();
        $product->setData(array());
        if ($stockItem = $product->getStockItem()) {
            $stockItem->setData(array());
        }

        if (empty($importData['store'])) {
            if (!is_null($this->getBatchParams('store'))) {
                $store = $this->getStoreById($this->getBatchParams('store'));
            } else {
                $message = Mage::helper('catalog')->__('Skip import row, required field "%s" not defined', 'store');
                Mage::throwException($message);
            }
        } else {
            $store = $this->getStoreByCode($importData['store']);
        }

        if ($store === false) {
            $message = Mage::helper('catalog')->__('Skip import row, store "%s" field not exists', $importData['store']);
            Mage::throwException($message);
        }
        if (empty($importData['sku'])) {
            $message = Mage::helper('catalog')->__('Skip import row, required field "%s" not defined', 'sku');
            Mage::throwException($message);
        }
        $product->setStoreId($store->getId());
        $productId = $product->getIdBySku($importData['sku']);
        $new = true; // fix for duplicating attributes error
        if ($productId) {
            $product->load($productId);
            $new = false; // fix for duplicating attributes error
        }
        $productTypes = $this->getProductTypes();
        $productAttributeSets = $this->getProductAttributeSets();

        /**
         * Check product define type
         */
        if (empty($importData['type']) || !isset($productTypes[strtolower($importData['type'])])) {
            $value = isset($importData['type']) ? $importData['type'] : '';
            $message = Mage::helper('catalog')->__('Skip import row, is not valid value "%s" for field "%s"', $value, 'type');
            Mage::throwException($message);
        }
        $product->setTypeId($productTypes[strtolower($importData['type'])]);
        /**
         * Check product define attribute set
         */
        if (empty($importData['attribute_set']) || !isset($productAttributeSets[$importData['attribute_set']])) {
            $value = isset($importData['attribute_set']) ? $importData['attribute_set'] : '';
            $message = Mage::helper('catalog')->__('Skip import row, is not valid value "%s" for field "%s"', $value, 'attribute_set');
            Mage::throwException($message);
        }
        $product->setAttributeSetId($productAttributeSets[$importData['attribute_set']]);

        foreach ($this->_requiredFields as $field) {
            $attribute = $this->getAttribute($field);
            if (!isset($importData[$field]) && $attribute && $attribute->getIsRequired()) {
                $message = Mage::helper('catalog')->__('Skip import row, required field "%s" for new products not defined', $field);
                Mage::throwException($message);
            }
        }

        //================================================
        // this part handles configurable products and links
        //================================================

        if ($importData['type'] == 'configurable') {
            $product->setCanSaveConfigurableAttributes(true);
            $configAttributeCodes = $this->userCSVDataAsArray($importData['config_attributes']);
            $usingAttributeIds = array();
            foreach ($configAttributeCodes as $attributeCode) {
                $attribute = $product->getResource()->getAttribute($attributeCode);
                if ($product->getTypeInstance()->canUseAttribute($attribute)) {
                    if ($new) { // fix for duplicating attributes error
                        $usingAttributeIds[] = $attribute->getAttributeId();
                    }
                }
            }
            if (!empty($usingAttributeIds)) {
                $product->getTypeInstance()->setUsedProductAttributeIds($usingAttributeIds);

                $configurableAttributesArray = $product->getTypeInstance()->getConfigurableAttributesAsArray();
                foreach ($configurableAttributesArray as &$configurableAttributeArray) {

                    $configurableAttributeArray['use_default'] = 1;
                    $configurableAttributeArray['position'] = 0;

                    if (isset($configurableAttributeArray['frontend_label'])) {
                        // Use the frontend_label as label, if available
                        $configurableAttributeArray['label'] = $configurableAttributeArray['frontend_label'];
                    } else {
                        // Use the attribute_code as a label
                        $configurableAttributeArray['label'] = $configurableAttributeArray['attribute_code'];
                    }
                }
                $product->setConfigurableAttributesData($configurableAttributesArray);

                $product->setCanSaveConfigurableAttributes(true);
                $product->setCanSaveCustomOptions(true);
            }
            if (isset($importData['associated'])) {
                $product->setConfigurableProductsData($this->skusToIds($importData['associated'], $product));
            }
        }

        /**
         * Init product links data (related, upsell, crosssell, grouped)
         */
        if (isset($importData['related'])) {
            $linkIds = $this->skusToIds($importData['related'], $product);
            if (!empty($linkIds)) {
                $product->setRelatedLinkData($linkIds);
            }
        }
        if (isset($importData['upsell'])) {
            $linkIds = $this->skusToIds($importData['upsell'], $product);
            if (!empty($linkIds)) {
                $product->setUpSellLinkData($linkIds);
            }
        }
        if (isset($importData['crosssell'])) {
            $linkIds = $this->skusToIds($importData['crosssell'], $product);
            if (!empty($linkIds)) {
                $product->setCrossSellLinkData($linkIds);
            }
        }
        if (isset($importData['grouped'])) {
            $linkIds = $this->skusToIds($importData['grouped'], $product);
            if (!empty($linkIds)) {
                $product->setGroupedLinkData($linkIds);
            }
        }

        //================================================





        if (isset($importData['category_ids'])) {
            $product->setCategoryIds($importData['category_ids']);
        }

        if (isset($importData['cross_sells']) && !empty($importData['cross_sells'])) {
            $crossSellsSku = explode(',', preg_replace( '/\s+/', '', $importData['cross_sells']));
            $this->setCrossSellsProducts($product, $crossSellsSku);
        } else {
            $product->setCrossSellLinkData($product, array()); // Cross sells products not found
        }

        foreach ($this->_ignoreFields as $field) {
            if (isset($importData[$field])) {
                unset($importData[$field]);
            }
        }

        if ($store->getId() != 0) {
            $websiteIds = $product->getWebsiteIds();
            if (!is_array($websiteIds)) {
                $websiteIds = array();
            }
            if (!in_array($store->getWebsiteId(), $websiteIds)) {
                $websiteIds[] = $store->getWebsiteId();
            }
            $product->setWebsiteIds($websiteIds);
        }

        if (isset($importData['websites'])) {
            $websiteIds = $product->getWebsiteIds();
            if (!is_array($websiteIds)) {
                $websiteIds = array();
            }
            $websiteCodes = explode(',', $importData['websites']);
            foreach ($websiteCodes as $websiteCode) {
                try {
                    $website = Mage::app()->getWebsite(trim($websiteCode));
                    if (!in_array($website->getId(), $websiteIds)) {
                        $websiteIds[] = $website->getId();
                    }
                } catch (Exception $e) {

                }
            }
            $product->setWebsiteIds($websiteIds);
            unset($websiteIds);
        }

        foreach ($importData as $field => $value) {
            if (in_array($field, $this->_inventoryFields)) {
                continue;
            }
            if (in_array($field, $this->_imageFields)) {
                continue;
            }

            $attribute = $this->getAttribute($field);
            if (!$attribute) {
                continue;
            }

            $isArray = false;
            $setValue = $value;

            if ($attribute->getFrontendInput() == 'multiselect') {
                $value = explode(self::MULTI_DELIMITER, $value);
                $isArray = true;
                $setValue = array();
            }

            if ($value && $attribute->getBackendType() == 'decimal') {
                $setValue = $this->getNumber($value);
            }

            if ($attribute->usesSource()) {
                $options = $attribute->getSource()->getAllOptions(false);

                if ($isArray) {
                    foreach ($options as $item) {
                        if (in_array($item['label'], $value)) {
                            $setValue[] = $item['value'];
                        }
                    }
                } else {
                    $setValue = null;
                    foreach ($options as $item) {
                        if ($item['label'] == $value) {
                            $setValue = $item['value'];
                        }
                    }
                }
            }

            $product->setData($field, $setValue);
        }

        if (!$product->getVisibility()) {
            $product->setVisibility(Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE);
        }

        $stockData = array();
        $inventoryFields = isset($this->_inventoryFieldsProductTypes[$product->getTypeId()]) ? $this->_inventoryFieldsProductTypes[$product->getTypeId()] : array();
        foreach ($inventoryFields as $field) {
            if (isset($importData[$field])) {
                if (in_array($field, $this->_toNumber)) {
                    $stockData[$field] = $this->getNumber($importData[$field]);
                } else {
                    $stockData[$field] = $importData[$field];
                }
            }
        }
        $product->setStockData($stockData);

        if ($updateImages) {
            //Added to remove all images for product before uploading new images
            if ($removeAllImages) {
                //check if gallery attribute exists then remove all images if it exists
                //Get products gallery attribute
                $attributes = $product->getTypeInstance()->getSetAttributes();
                if (isset($attributes['media_gallery'])) {
                    $gallery = $attributes['media_gallery'];
                    //Get the images
                    $galleryData = $product->getMediaGallery();
                    foreach ($galleryData['images'] as $image) {
                        //If image exists
                        if ($gallery->getBackend()->getImage($product, $image['file'])) {
                            $gallery->getBackend()->removeImage($product, $image['file']);
                        }
                    }
                }
                #$gallery->clearMediaAttribute($product, array('image','small_image','thumbnail'));
            }
            //END Remove Images

            $imageData = array();
            foreach ($this->_imageFields as $field) {
                if (!empty($importData[$field]) && $importData[$field] != 'no_selection') {
                    if (!isset($imageData[$importData[$field]])) {
                        $imageData[$importData[$field]] = array();
                    }
                    $imageData[$importData[$field]][] = $field;
                }
            }

            foreach ($imageData as $file => $fields) {
                try {

                    $visibility = null;
                    if ($fields[0] == 'image_1') {
                        $visibility = array("image", "small_image", "thumbnail");
                        $label = '';
                        $sort = 1;
                    }
                    if ($fields[0] == 'image_2') {
                        $label = '';
                        $sort = 2;
                    }
                    if ($fields[0] == 'image_3') {
                        $label = '';
                        $sort = 3;
                    }
                    if ($fields[0] == 'image_4') {
                        $label = '';
                        $sort = 4;
                    }
                    if ($fields[0] == 'image_5') {
                        $label = '';
                        $sort = 5;
                    }

                    $product->addImageToMediaGallery(Mage::getBaseDir('media') . DS . 'import' . $file, $visibility, false, false, $label, $sort);
                } catch (Exception $e) {

                }
            }
        }

        $product->setIsMassupdate(true);
        $product->setExcludeUrlRewrite(true);

        $product->save();

        return true;
    }

    protected function userCSVDataAsArray($data) {
        return explode(',', str_replace(" ", "", $data));
    }

    protected function skusToIds($userData, $product) {
        $productIds = array();
        foreach ($this->userCSVDataAsArray($userData) as $oneSku) {
            if (($a_sku = (int) $product->getIdBySku($oneSku)) > 0) {
                parse_str("position=", $productIds[$a_sku]);
            }
        }
        return $productIds;
    }

    /**
     * Set Cross Sells To product
     *
     * @param $product
     * @param $crossSellsSku
     * @return bool
     */
    public function setCrossSellsProducts($product, $crossSellsSku)
    {
        $params = array();
        if (!count($crossSellsSku)) {
            $product->setCrossSellLinkData($params); // Cross sells products not found

            return true;
        }

        $crossSellsSku = array_combine(range(1, count($crossSellsSku)), $crossSellsSku);
        foreach ($crossSellsSku as $position => $sku) {
            $productId = Mage::getModel("catalog/product")->getIdBySku($sku);
            if ($productId) {
                $params[$productId] = array('position' => $position);
            }
        }

        $product->setCrossSellLinkData($params);

        return true;
    }
}
