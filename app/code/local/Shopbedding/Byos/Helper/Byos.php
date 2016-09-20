<?php

class Shopbedding_Byos_Helper_Byos extends Mage_Core_Helper_Abstract
{
    /**
     * if this product is a child of a BYOS, return that BYOS's id
     * else return null
     *
     * @param <type> $product
     * @return <type>
     */
    public function getByos($product) {
        $parentIds = $this->getParentIds($product);
        foreach($parentIds as $id) {
            $parent = Mage::getModel('catalog/product')->load($id);
            $categories = $parent->getCategoryIds();
            foreach ($categories as $categoryId) {
                $category = Mage::getModel('catalog/category')->load($categoryId);
                if (in_array('47', $category->getPathIds())) {
                    return $id;
                }
            }
        }
        return null;
    }


    public function getParentIds($product) {
        /*$resourceModel = Mage::getResourceModel('catalog/product');
        $parentIds = $resourceModel->getParentProductIds($product);*/
        $configurable_product_model = Mage::getModel('catalog/product_type_grouped');
        $parentIds = $configurable_product_model->getParentIdsByChild($product->getId());
        return $parentIds;
    }
}
?>
