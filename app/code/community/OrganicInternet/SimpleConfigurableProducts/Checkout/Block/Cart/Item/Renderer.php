<?php
class OrganicInternet_SimpleConfigurableProducts_Checkout_Block_Cart_Item_Renderer
    extends Mage_Checkout_Block_Cart_Item_Renderer
{
    private $productModel;
    private $productResModel;

    protected function getConfigurableProductParentId()
    {
        if ($this->getItem()->getOptionByCode('cpid')) {
            return $this->getItem()->getOptionByCode('cpid')->getValue();
        }
        return null;
    }

    protected function getConfigurableProductParent()
    {
        return Mage::getModel('catalog/product')
            ->setStoreId(Mage::app()->getStore()->getId())
            ->load($this->getConfigurableProductParentId());
    }

    public function getProduct()
    {
        return Mage::getModel('catalog/product')
           ->setStoreId(Mage::app()->getStore()->getId())
                ->load($this->getItem()->getProductId());
    }

    public function getProductName()
    {
        if (Mage::getStoreConfig('SCP_options/cart/show_configurable_product_name')
            && $this->getConfigurableProductParentId()) {
            return $this->getConfigurableProductParent()->getName();
        } else {
            return parent::getProductName();
        }
    }


    /* Bit of a hack this - assumes configurable parent is always linkable */
    public function hasProductUrl()
    {
        if ($this->getConfigurableProductParentId()) {
            return true;
        } else {
            return parent::hasProductUrl();
        }
    }

    public function getProductUrl()
    {
        if ($this->getConfigurableProductParentId()) {
            return $this->getConfigurableProductParent()->getProductUrl();
        } else {
            return parent::getProductUrl();
            #return $this->getProduct()->getProductUrl();
        }
    }

    public function getOptionList()
    {
        $options = false;
        if (Mage::getStoreConfig('SCP_options/cart/show_custom_options')) {
            $options = parent::getOptionList();
        }

        if (Mage::getStoreConfig('SCP_options/cart/show_config_product_options')) {
            $opts = $this->getItem()->getOptionByCode('opts');
            if ($opts) {
                Mage::log('options already set');
                $options = Zend_Json::decode($opts->getValue());
            } else {
                if ($this->getConfigurableProductParentId()) {
if ($this->getConfigurableProductParent()->getTypeId() == 'configurable'){
                    $attributes = $this->getConfigurableProductParent()
                        ->getTypeInstance()
                        ->getUsedProductAttributes();
                    foreach($attributes as $attribute) {
                        $options[] = array(
                            'label' => $attribute->getFrontendLabel(),
                            'value' => $this->getProduct()->getAttributeText($attribute->getAttributeCode()),
                            'option_id' => $attribute->getId(),
                        );
                    }
                    $this->getItem()->addOption(array('code'=>'opts', 'value'=>  Zend_Json::encode($options)))->save();
}
                }
            }
        }
        return $options;
    }

    
    public function getProductThumbnail()
    {
      
        if (!Mage::getStoreConfig('SCP_options/cart/show_configurable_product_image')) {
            $childThumbnail = parent::getProductThumbnail();

                return $childThumbnail;
            #If image is not placeholder...
/*            if(strpos($childThumbnail, Mage::helper('catalog/image')->getPlaceHolder($this->getProduct())) === FALSE) {
                return $childThumbnail;
            }*/
        }
            
        #If we're showing parents anyway, or we can't show the child, show the parent.
        #If there's no image then a placeholder will be shown
        if ($this->getConfigurableProductParentId()) {
            $parentProduct = $this->getConfigurableProductParent();
            return $this->helper('catalog/image')->init($parentProduct, 'thumbnail');
        }

        return $this->helper('catalog/image')->init($this->getProduct(), 'thumbnail');

    }

    public function setParentProductId()
    {
        $product = $this->getItem()->getProduct();
        if ($product->getVisibility() == 1) {
            if (!isset($this->productResModel)) {
                $this->productResModel = Mage::getResourceModel('catalog/product');
            }
            //fix getParentProductIds deprecated after 1.4.2.0
            $configurable_product_model = Mage::getModel('catalog/product_type_configurable');
            $parentIds = $configurable_product_model->getParentIdsByChild($product->getId());
            //$parentIds = $this->productResModel->getParentProductIds($product);
            //fix
            if (!empty($parentIds)) {
                if (!isset($this->productModel)) {
                    $this->productModel = Mage::getModel('catalog/product');
                }
                $parent = $this->productModel->load($parentIds[0]);
                $this->getItem()->addOption(array('code'=>'cpid', 'value'=>$parentIds[0]));
            }
        }
    }

}
