<?php

// Generate Json config for simpleconfigurableproducts to speed things up

class Mage_Catalog_Block_Product_View_Scpconfig extends Mage_Catalog_Block_Product_Abstract
{


    protected function _construct()
    {
        // cache forever
        $this->addData( array( 'cache_lifetime'    => false, ) );
    }


    public function getCacheTags()
    {
        return array(Mage_Catalog_Model_Product::CACHE_TAG . "_" . $this->getProduct()->getId());
    }


    public function getCacheKey()
    {
        return "SCPCONFIG_".$this->getProduct()->getId();
    } 



    /**
     * Get JSON encripted configuration array which can be used for JS dynamic
     * price calculation depending on product options
     *
     * @return string
     */
    public function getJsonConfig()
    {

        Mage::log('hit getJsonConfig');
        $start = microtime(true);

        $taxHelper = Mage::helper('tax');
        $coreHelper = Mage::helper('core');

        $config = array();
        if (!$this->hasOptions()) {
            return $coreHelper->jsonEncode($config);
        }

        $_request = Mage::getSingleton('tax/calculation')->getRateRequest(false, false, false);
        $_request->setProductClassId($this->getProduct()->getTaxClassId());
        $defaultTax = Mage::getSingleton('tax/calculation')->getRate($_request);
        $_request = Mage::getSingleton('tax/calculation')->getRateRequest();
        $_request->setProductClassId($this->getProduct()->getTaxClassId());
        $currentTax = Mage::getSingleton('tax/calculation')->getRate($_request);
        $_regularPrice = $this->getProduct()->getPrice();
        $_finalPrice = $this->getProduct()->getFinalPrice();
        $_priceInclTax = $taxHelper->getPrice($this->getProduct(), $_finalPrice, true);
        $_priceExclTax = $taxHelper->getPrice($this->getProduct(), $_finalPrice);
        $config = array(
            'productId'           => $this->getProduct()->getId(),
            'priceFormat'         => Mage::app()->getLocale()->getJsPriceFormat(),
            'includeTax'          => $taxHelper->priceIncludesTax() ? 'true' : 'false',
            'showIncludeTax'      => $taxHelper->displayPriceIncludingTax(),
            'showBothPrices'      => $taxHelper->displayBothPrices(),
            'productPrice'        => $coreHelper->currency($_finalPrice, false, false),
            'productOldPrice'     => $coreHelper->currency($_regularPrice, false, false),
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

        Mage::log('time was '.(microtime(true)-$start));
        return Mage::helper('core')->jsonEncode($config);
    }


    public function hasOptions()
    {
        if ($this->getProduct()->getTypeInstance(true)->hasOptions($this->getProduct())) {
            return true;
        }
        return false;
    }

    var $_product = null;


    public function getParentBlock()
    {
        return $this->getLayout()->getBlockSingleton('catalog/product_view');
    }

    public function getProduct()
    {

        if ( ! isset( $this->_product ) ) {
            // get product from parent block
            $this->_product = $this->getParentBlock()->getProduct();
        }
        
        return $this->_product;
    }




}
