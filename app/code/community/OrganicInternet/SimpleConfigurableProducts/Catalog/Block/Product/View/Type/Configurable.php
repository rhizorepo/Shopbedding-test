<?php

class OrganicInternet_SimpleConfigurableProducts_Catalog_Block_Product_View_Type_Configurable
    extends Mage_Catalog_Block_Product_View_Type_Configurable
{
    private $imageHelper;
    private $alertHelper;
    private $priceConversions = array();

    public function getJsonConfig($encode = true, $prodImageSizes = array())
    {
        $config = Zend_Json::decode(parent::getJsonConfig());

        $childProducts = array();

        #initialize variables
        $changeName = Mage::getStoreConfig('SCP_options/product_page/change_name');
        $changeDesc = Mage::getStoreConfig('SCP_options/product_page/change_description');
        $changeShortDesc = Mage::getStoreConfig('SCP_options/product_page/change_short_description');
        $changeOptions = Mage::getStoreConfig('SCP_options/product_page/change_attributes');
        $changeImage = Mage::getStoreConfig('SCP_options/product_page/change_image');
        $changeImageFancy = Mage::getStoreConfig('SCP_options/product_page/change_image_fancy');
        $showPriceRange = Mage::getStoreConfig('SCP_options/product_page/show_price_ranges_in_options');
        if (!isset($this->imageHelper)) { $this->imageHelper = Mage::helper('catalog/image'); }
        $imagesByColor = array();

        $typeInstance = $this->getProduct()->getTypeInstance(TRUE);

        //Create the extra price and tier price data/html we need.
        foreach ($this->getAllowProducts() as $product) {
            $productId  = $product->getId();

            //product stock
            $inStock = $product->getStockItem()->getIsInStock();
            $stockNotify = "";
            if (!$inStock){
                if (!isset($this->alertHelper)) {
                    $this->alertHelper = Mage::helper('productalert');
                }
                $stockNotify = $this->alertHelper->getSaveUrlByProductId('stock', $productId);
            }

            //prices
            $price = $_price = $product->getPrice();
            if (!isset($this->priceConversions[$_price])) {
                $price = $this->_registerJsPrice($this->_convertPrice($_price));
                $this->priceConversions[$_price] = $price;
            } else {
                $price = $this->priceConversions[$_price];
            }
            if($product->getTypeId() === 'simple') {
                $finalPrice = $_finalPrice = $product->getFinalPrice();
            } else {
                $finalPrice = $_finalPrice = $typeInstance->getFinalPriceForUsedProduct($product->getId());
            }
            if($finalPrice === FALSE) {
                $finalPrice = $_finalPrice = $product->getFinalPrice();
            }
            if (!isset($this->priceConversions[$_finalPrice])) {
                $finalPrice = $this->_registerJsPrice($this->_convertPrice($_finalPrice));
                $this->priceConversions[$_finalPrice] = $finalPrice;
            } else {
                $finalPrice = $this->priceConversions[$_finalPrice];
            }
			
			/*-------added by cw--------------*/
			$color = $product->getColor() ? $product->getColor() : 0;
			$size = $product->getSize() ? $product->getSize() : 0;
			$drop = $product->getDropLength() ? $product->getDropLength(): 0;
			$combination = sprintf("%02d", $size)."-".sprintf("%02d", $drop)."-".sprintf("%02d", $color);
			/*-----------------------------------*/
			
			
            //default attributes
            $childProducts[$productId] = array(
                "price" => $price,
                "finalPrice" => $finalPrice,
                "sku" => $product->getSku(),
                "availability" => $inStock,
                "notifyLink" => $stockNotify,
                // additional for qty check on frontend
				"combination" => $combination,
                "qty" => (int)$product->getStockItem()->getQty()
                //
            );

            if ($changeName) {
                $childProducts[$productId]["productName"] = $product->getName();
            }
            if ($changeDesc) {
                $childProducts[$productId]["description"] = $product->getDescription();
            }
            if ($changeShortDesc) {
                $childProducts[$productId]["shortDescription"] = $product->getShortDescription();
            }

            if ($changeOptions) {
                $childBlock = $this->getLayout()->createBlock('catalog/product_view_attributes');
                $childProducts[$productId]["productAttributes"] = $childBlock->setTemplate('catalog/product/view/attributes.phtml')
                    ->setProduct($product)
                    ->toHtml();
            }

            #if image changing is enabled..
            if ($changeImage) {
                #but dont bother if fancy image changing is enabled
                if (!$changeImageFancy) {

                    // we are assuming that the only attribute to change a product's
                    // image is its color.
                    // i.e. two different sizes/depths/drop lengths with the same
                    // color will have the same image
                    $color = $product->getColor();
                    $placeholder = false;
                    if(!isset($imagesByColor[$color])) {
                        $image = $this->imageHelper->init($product, 'image');
                        $imageString = (string)$image;
                        $imagesByColor[$color]["full"] = $imageString;
                        $imagesByColor[$color]["lightbox"] = (string)$image->resize(700);
                        $imagesByColor[$color]["swatch"] = $product->getSwatchImage();
                        $imagesByColor[$color]["swatchLabel"] = $product->getSwatchImageLabel();
                        $imagesByColor[$color]["label"] = $product->getImageLabel();
                        foreach ($prodImageSizes as $size) {
                            $imagesByColor[$color][(string)$size] = (string)$image->resize($size);
                        }
                        if(strpos($imageString, '/placeholder/') !== FALSE) {
                            $placeholder = true;
                        }
                        
                    }
                    if (!$placeholder) {
                        $childProducts[$productId]["fullImageUrl"] = $imagesByColor[$color]["full"];
                        $childProducts[$productId]["lightboxImageUrl"] = $imagesByColor[$color]["lightbox"];
                    }
//                    #If image is not placeholder...  shame there's not a proper method for this as this method while seeminly reliable isn't great
//                    if(strpos($imageString, '/placeholder/') === FALSE) {
//                        #add child product image url to json spConfig var
//                        $childProducts[$productId]["fullImageUrl"] = $imageString;
//                        $childProducts[$productId]["lightboxImageUrl"] = (string)$image->resize(700);
//                        //(!empty($product["image_label"])) ? Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA).'productImages/700/'.$product['image_label']  : (string)Mage::helper('catalog/image')->init($product, 'image')->resize(700);
//                    }
                }
            }
        }

        //colors (SHB specific)
        $config['swatches'] = $imagesByColor;

        //Remove any existing option prices.
        //Removing holes out of existing arrays is not nice,
        //but it keeps the extension's code separate so if Varien's getJsonConfig
        //is added to, things should still work.
        if (is_array($config['attributes'])) {
            foreach ($config['attributes'] as $attributeID => &$info) {
                if (is_array($info['options'])) {
                    foreach ($info['options'] as &$option) {
                        unset($option['price']);
                    }
                    unset($option); //clear foreach var ref
                }
            }
            unset($info); //clear foreach var ref
        }

        $p = $this->getProduct();
        $config['childProducts'] = $childProducts;
        if ($p->getMaxPossibleFinalPrice() != $p->getFinalPrice()) {
            $config['priceFromLabel'] = $this->__('<strong>Price starts at:</strong>');
        } else {
            $config['priceFromLabel'] = $this->__('Price:');
        }
        $config['ajaxBaseUrl'] = Mage::getUrl('oi/ajax/');
        $config['productName'] = $p->getName();
        $config['description'] = $p->getDescription();
        $config['shortDescription'] = $p->getShortDescription();
        if ($changeImage) {
            $config["imageUrl"] = $this->imageHelper->init($p, 'image');
        }

        $childBlock = $this->getLayout()->createBlock('catalog/product_view_attributes');
        $config["productAttributes"] = $childBlock->setTemplate('catalog/product/view/attributes.phtml')
            ->setProduct($p)
            ->toHtml();
        if ($changeImage) {
            if ($changeImageFancy) {
                $childBlock = $this->getLayout()->createBlock('catalog/product_view_media');
                $config["imageZoomer"] = $childBlock->setTemplate('catalog/product/view/media.phtml')
                    ->setProduct($p)
                    ->toHtml();
            }
        }
        if ($showPriceRange) {
            $config['showPriceRangesInOptions'] = true;
            $config['rangeToLabel'] = $this->__('to');

        }
        //Mage::log($config);
        $return = $config;
        if ($encode) { // true by default
            $return = Zend_Json::encode($config);
        }
        return $return;
        //parent getJsonConfig uses the following instead, but it seems to just break inline translate of this json?
        //return Mage::helper('core')->jsonEncode($config);
    }


   /**
     * Get JSON encripted configuration array which can be used for JS dynamic
     * price calculation depending on product options
     * <<<Stolen from Mage_Catalog_Block_Product_View>>>
     *
     * @return string
     */
    public function getJsonConfigPrice($product)
    {
        $config = array();
//        if (!$this->hasOptions()) {
//            return Mage::helper('core')->jsonEncode($config);
//        }

        $_request = Mage::getSingleton('tax/calculation')->getRateRequest(false, false, false);
        $_request->setProductClassId($product->getTaxClassId());
        $defaultTax = Mage::getSingleton('tax/calculation')->getRate($_request);

        $_request = Mage::getSingleton('tax/calculation')->getRateRequest();
        $_request->setProductClassId($product->getTaxClassId());
        $currentTax = Mage::getSingleton('tax/calculation')->getRate($_request);

        $_regularPrice = $product->getPrice();
        $_finalPrice = $product->getFinalPrice();
        $_priceInclTax = Mage::helper('tax')->getPrice($product, $_finalPrice, true);
        $_priceExclTax = Mage::helper('tax')->getPrice($product, $_finalPrice);

        $config = array(
            'productId'           => $product->getId(),
            'priceFormat'         => Mage::app()->getLocale()->getJsPriceFormat(),
            'includeTax'          => Mage::helper('tax')->priceIncludesTax() ? 'true' : 'false',
            'showIncludeTax'      => Mage::helper('tax')->displayPriceIncludingTax(),
            'showBothPrices'      => Mage::helper('tax')->displayBothPrices(),
            'productPrice'        => Mage::helper('core')->currency($_finalPrice, false, false),
            'productOldPrice'     => Mage::helper('core')->currency($_regularPrice, false, false),
            'skipCalculate'       => ($_priceExclTax != $_priceInclTax ? 0 : 1),
            'defaultTax'          => $defaultTax,
            'currentTax'          => $currentTax,
            'idSuffix'            => '_clone',
            'oldPlusDisposition'  => 0,
            'plusDisposition'     => 0,
            'oldMinusDisposition' => 0,
            'minusDisposition'    => 0,
        );

        $responseObject = new Varien_Object();
        Mage::dispatchEvent('catalog_product_view_config', array('response_object'=>$responseObject));
        if (is_array($responseObject->getAdditionalOptions())) {
            foreach ($responseObject->getAdditionalOptions() as $option=>$value) {
                $config[$option] = $value;
            }
        }

        return Mage::helper('core')->jsonEncode($config);
    }

    public function getJsonConfigProduct($product, $encode = true, $prodImageSizes)
    {
        $this->_product = $product;

        // since there are multiple products on a page, we were running into issues
        // with the "getAllowedProducts()" running correctly for each product.
        // this should fix that.
        $products = array();
if ($product->getTypeId() == 'configurable'){
        $allProducts = $product->getTypeInstance(true)
            ->getUsedProducts(null, $product);
        foreach ($allProducts as $product) {
            //if ($product->isSaleable()) {
                $products[] = $product;
            //}
        }
        $this->setAllowProducts($products);
}
        return $this->getJsonConfig($encode, $prodImageSizes);
    }
}
