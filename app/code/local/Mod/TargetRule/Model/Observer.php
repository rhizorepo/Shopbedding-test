<?php
/**
 * Class Mod_TargetRule_Model_Observer
 */
class Mod_TargetRule_Model_Observer
{
    /**
     * In case when client add product from "cross-sell" block we must remove this product from "cross-sell"
     * collection and add it to cart.
     *
     * If this product is simple we must also exclude its parent product
     *
     * @param Varien_Event_Observer $observer
     * @return $this
     */
    public function excludeParentProducts(Varien_Event_Observer $observer)
    {
        $excludeProductIds = $observer->getEvent()->getExcludeProductsIds();

        $excludeChildProductIds = $excludeProductIds->getData();
        $excludeParentProductIds = array();
        foreach ($excludeChildProductIds  as $productId) {
            if ($parentId = Mage::getResourceSingleton('catalog/product_type_configurable')->getParentIdsByChild($productId)) {
                $excludeParentProductIds[] = (int)current($parentId);
            }
        }

        // add parent products to exclude
        $excludeIds = array_merge($excludeChildProductIds, $excludeParentProductIds);
        $excludeProductIds->setData($excludeIds);

        return $this;
    }

    /**
     * Add not visible products to cross-sell collection. It is needed for displaying simple products
     * in cross-sell block
     *
     * @param Varien_Event_Observer $observer
     * @return $this
     */
    public function addNotVisibleProducts(Varien_Event_Observer $observer)
    {
        $crossellsProductsCollection = $observer->getEvent()->getTargetLinkCollection();
        $this->addVisibleProductsToCrossellsProducts($crossellsProductsCollection);

        return $this;
    }

    /**
     * @param Mage_Eav_Model_Entity_Collection_Abstract $collection
     * @return $this
     */
    protected function addVisibleProductsToCrossellsProducts(Mage_Eav_Model_Entity_Collection_Abstract $collection)
    {
        // Add visible all products in Enterprise_TargetRule_Block_Checkout_Cart_Crosssell
        $collection->setVisibility(array(
            Mage_Catalog_Model_Product_Visibility::VISIBILITY_IN_SEARCH,
            Mage_Catalog_Model_Product_Visibility::VISIBILITY_IN_CATALOG,
            Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH,
            Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE
        ));

        return $this;
    }
}