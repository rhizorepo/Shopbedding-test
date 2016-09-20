<?php
/**
 * TargetRule Checkout Cart Cross-Sell Products Block
 *
 * @category   Mod
 * @package    Mod_TargetRule
 */
class Mod_TargetRule_Block_Checkout_Cart_Crossell extends Enterprise_TargetRule_Block_Checkout_Cart_Crosssell
{
    /**
     * Get exclude product ids
     *
     * @return array
     */
    protected function _getExcludeProductIds()
    {
        $excludeProductIds = $this->_getCartProductIds();
        if (!is_null($this->_items)) {
            $excludeProductIds = array_merge(array_keys($this->_items), $excludeProductIds);
        }

        // change exclude product ids
        $excludeIds = new Varien_Object($excludeProductIds);
        Mage::dispatchEvent('targetrule_block_checkout_cart_crossell_get_exclude_product_ids',
            array('exclude_products_ids' => $excludeIds)
        );
        $excludeProductIds = $excludeIds->getData();

        return $excludeProductIds;
    }

    /**
     * Get link collection for cross-sell
     *
     * @throws Mage_Core_Exception
     * @return Mage_Catalog_Model_Resource_Product_Collection|null
     */
    protected function _getTargetLinkCollection()
    {
        /* @var $collection Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Link_Product_Collection */
        $collection = Mage::getModel('catalog/product_link')
            ->useCrossSellLinks()
            ->getProductCollection()
            ->setStoreId(Mage::app()->getStore()->getId())
            ->setGroupBy();
        $this->_addProductAttributesAndPrices($collection);

        Mage::getSingleton('catalog/product_visibility')
            ->addVisibleInSiteFilterToCollection($collection);

        // set all visible products for "cross-sell" block
        Mage::dispatchEvent('targetrule_block_checkout_cart_crossell_get_target_link_collection',
            array('target_link_collection' => $collection)
        );

        Mage::getSingleton('cataloginventory/stock_status')
            ->addIsInStockFilterToCollection($collection);

        return $collection;
    }
}