<?php

class Mage_Catalog_Helper_Byos extends Mage_Core_Helper_Abstract
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
        if (!empty($parentIds)) {
            $_productModel = Mage::getModel('catalog/product');
            $_catModel = Mage::getModel('catalog/category');
            foreach($parentIds as $id) {
                $parent = $_productModel->load($id);
                $categories = $parent->getCategoryIds();
                foreach ($categories as $categoryId) {
                    // This way only works if parent is directly in Category 47,
                    // Not a child of it
                    // Otherwise use commented method below
                    if ($categoryId == '47') {
                        return $id;
                    }
//                    $category = $_catModel->load($categoryId);
//                    if (in_array('47', $category->getPathIds())) {
//                        return $id;
//                    }
                }
            }
        }
        return null;
    }


    private function getParentIds($product) {
        //$resourceModel = Mage::getResourceModel('catalog/product');
        //$parentIds = $resourceModel->getParentProductIds($product);
        $configurable_product_model = Mage::getModel('catalog/product_type_grouped');
        $parentIdArray = $configurable_product_model->getParentIdsByChild($product->getId());
        return $parentIdArray;
    }
}
?>
