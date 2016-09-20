<?php
class Shopbedding_Collection_Model_Catalog_Resource_Eav_Mysql4_Product_Attribute_Backend_Media extends Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Attribute_Backend_Media
{
    public function loadGallery($product, $object)
    {
        // Select gallery images for product
        $select = $this->_getReadAdapter()->select()
            ->from(
                array('main'=>$this->getMainTable()),
                array('value_id', 'value AS file')
            )
            ->joinLeft(
                array('value'=>$this->getTable(self::GALLERY_VALUE_TABLE)),
                'main.value_id=value.value_id AND value.store_id='.'0',//(int)$product->getStoreId(),
                array('label','position','disabled','is_category','swatchimage','swatchimage_label')
            )
            ->joinLeft( // Joining default values
                array('default_value'=>$this->getTable(self::GALLERY_VALUE_TABLE)),
                'main.value_id=default_value.value_id AND default_value.store_id=0',
                array(
                    'label_default' => 'label',
                    'position_default' => 'position',
                    'disabled_default' => 'disabled'
                )
            )
            ->where('main.attribute_id = ?', $object->getAttribute()->getId())
            ->where('main.entity_id = ?', $product->getId())
            ->order('IF(value.position IS NULL, default_value.position, value.position) ASC');

        $result = $this->_getReadAdapter()->fetchAll($select);
        $this->_removeDuplicates($result);
        return $result;
    }
} 