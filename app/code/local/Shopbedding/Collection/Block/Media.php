<?php 
class Shopbedding_Collection_Block_Media extends Mage_Catalog_Block_Product_View_Media
{
	public function getProduct()
    {
        if ($this->getProductId() && !Mage::registry('product'.$this->getProductId())) {
            $product = Mage::getModel('catalog/product')->load($this->getProductId());
            Mage::register('product'.$this->getProductId(), $product);
        }
        return Mage::registry('product'.$this->getProductId());
    } 
    public function getCategoryGalleryImages($product) {
		if(!$product->hasData('catalog_media_gallery_images') && is_array($product->getMediaGallery('images'))) {
            $images = new Varien_Data_Collection();
            foreach ($product->getMediaGallery('images') as $image) {
                if (!$image['is_category']) {
                    continue;
                }
                $image['url'] = $product->getMediaConfig()->getMediaUrl($image['file']);
                $image['id'] = isset($image['value_id']) ? $image['value_id'] : null;
                $image['path'] = $product->getMediaConfig()->getMediaPath($image['file']);
                $images->addItem(new Varien_Object($image));
            }
            $product->setData('media_gallery_images', $images);
        }
		return $product->getData('media_gallery_images');
	}
}
