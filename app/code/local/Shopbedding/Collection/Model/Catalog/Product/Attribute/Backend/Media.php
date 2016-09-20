<?php
class Shopbedding_Collection_Model_Catalog_Product_Attribute_Backend_Media extends Mage_Catalog_Model_Product_Attribute_Backend_Media
{
    public function afterSave($object)
    {

        if ($object->getIsDuplicate() == true) {
            $this->duplicate($object);
            return;
        }

        $attrCode = $this->getAttribute()->getAttributeCode();
        $value = $object->getData($attrCode);
        if (!is_array($value) || !isset($value['images']) || $object->isLockedAttribute($attrCode)) {
            return;
        }
        $toDelete = array();
        $filesToValueIds = array();
//        print_r($value['images']);die;
        foreach ($value['images'] as &$image) {
            if(!empty($image['removed'])) {
                if(isset($image['value_id'])) {
                    $toDelete[] = $image['value_id'];
                }
                continue;
            }

            if(!isset($image['value_id'])) {
                $data = array();
                $data['entity_id']      = $object->getId();
                $data['attribute_id']   = $this->getAttribute()->getId();
                $data['value']          = $image['file'];
                $image['value_id']      = $this->_getResource()->insertGallery($data);
            }

            $this->_getResource()->deleteGalleryValueInStore($image['value_id'], $object->getStoreId());

            // Add per store labels, position, disabled
            $data = array();
            $data['value_id'] = $image['value_id'];
            $data['label']    = $image['label'];
            if(isset($image['swatchimage']))
                $data['swatchimage']    = $image['swatchimage'];
            if(isset($image['swatchimage_label']))
                $data['swatchimage_label']    = $image['swatchimage_label'];
            $data['position'] = (int) $image['position'];
            $data['disabled'] = (int) $image['disabled'];
            $data['store_id'] = (int) $object->getStoreId();
            if(isset($image['is_category']))
			    $data['is_category'] = (int) $image['is_category'];

            $this->_getResource()->insertGalleryValueInStore($data);
        }

        $this->_getResource()->deleteGallery($toDelete);
    }

} 
