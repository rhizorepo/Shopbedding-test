<?php

class HunterGroupCo_ConfigurableIndex_Model_Resource_Saleable  extends Mage_Core_Model_Resource_Db_Abstract {

    protected $_saleableData = array();
    protected $_colorsData = array();

    public function setCollection($collection) {
        $productIds = $collection->getAllIds();
        if(empty($productIds))
            return;
        $storeId = Mage::app()->getStore()->getId();
        $adapter = $this->_getReadAdapter();
        $tableName = Mage::getSingleton('core/resource')->getTableName('huntergroupco_configurableindex/saleable');
        $results = $adapter->fetchAll('select `product_id`, `is_saleable`, `colors` FROM `'.$tableName.'` WHERE `product_id` IN ('.implode(', ', $productIds).') AND `store_id` = ' . Mage::app()->getStore()->getStoreId());

        if(! empty($results)) foreach($results AS $r) {
            $this->_saleableData[$r['product_id']] = $r['is_saleable'];
            $this->_colorsData[$r['product_id']] = unserialize($r['colors']);
        }
    }

    public function isSaleable($product) {
        if(isset($this->_saleableData[$product->getId()])) {
            return $this->_saleableData[$product->getId()];
        }
        return $product->isSaleable();
    }

    public function getColors($id) {

        $adapter = $this->_getReadAdapter();
        $tableName = Mage::getSingleton('core/resource')->getTableName('huntergroupco_configurableindex/saleable');
        $results = $adapter->fetchAll('SELECT * FROM `'.$tableName.'` WHERE `product_id` = '.$id.' ORDER BY `product_id` ASC');

        if(! empty($results)) foreach($results AS $r) {
            $this->_saleableData[$r['product_id']] = $r['is_saleable'];
            $this->_colorsData[$r['product_id']] = unserialize($r['colors']);
        }

        if(isset($this->_colorsData[$id])) {
            return $this->_colorsData[$id];
        }
    }

    /**
     * Init main entity
     *
     */
    protected function _construct()
    {
        $this->_init('huntergroupco_configurableindex/saleable', 'id');
    }

    /**
     * Truncate table with booking information
     *
     * @return void
     */
    protected function clearAllIndexes() {
     $this->_getWriteAdapter()->truncateTable($this->getMainTable());

    }


    /**
     * Removes index data by ids
     *
     * @param Varien_DB_Adapter $_adapter
     * @param string $_tableName
     * @param array $_ids
     * @return void
     */
    protected function removeDataByIds($_adapter, $_tableName, $_ids ){
        $condition = array(
            'id IN (?)' => $_ids
        );

        $_adapter->delete($_tableName, $condition);
    }

    /**
     * Reindex all products
     *
     * @return void
     */
    public function refreshAllIndexes() {
       // $this->clearAllIndexes();
        $this->refreshSaleableData();
    }

    public function refreshSaleableData($_productIds = NULL) {
        $_productCollection = Mage::getModel('catalog/product')
            ->getCollection();
        if($_productIds != null){
            $_productCollection->addAttributeToFilter( 'entity_id',  array('in' => $_productIds) );
        }
        else {
            ini_set('memory_limit', '2048M');
            $_productCollection->addAttributeToFilter('type_id', 'configurable');
        }

        $data = array();
        if( count($_productCollection) > 0 ){
            foreach($_productCollection as $_product) {
                if($_product->getTypeId() == "simple"){
                    $_product->isSaleable(); // Because SQL requests are cached
                    $parentIds = Mage::getModel('catalog/product_type_grouped')->getParentIdsByChild($_product->getId());
                    if(!$parentIds)
                        $parentIds = Mage::getModel('catalog/product_type_configurable')->getParentIdsByChild($_product->getId());
                    if(isset($parentIds[0])){
                        $_product = Mage::getModel('catalog/product')->load($parentIds[0]);
                    }
                    else {
                        $_product = null;
                    }
                }
                if($_product) {
                    $this->saveSaleableData($_product);
                }
            }
        }
    }

    protected function saveSaleableData($_product) {
        $adapter = $this->_getWriteAdapter();
        $_stores = $_product->getStoreIds();
        $tableName = Mage::getSingleton('core/resource')->getTableName('huntergroupco_configurableindex/saleable');
        $query = '';
        $queryData = array();
        if(! empty($_stores)) foreach($_stores AS $store) {
            $product = Mage::getModel('catalog/product');
            $product->setStoreId($store)->load($_product->getId());

            $instance = $product->getTypeInstance(TRUE);
            $instance->setStoreFilter($product->getStoreId(), $product);
            if(method_exists($instance, 'getUsedProducts')) {
                $products = $instance->getUsedProducts(null, $product);
            } elseif(method_exists($instance, 'getAssociatedProducts')) {
                $products = $instance->getAssociatedProducts($product);
            } else {
                continue;
            }
            foreach($products AS $p)
            {
                Mage::unregister('isSaleable'.$p->getId());
            }
            // Saleable

            Mage::dispatchEvent('catalog_product_is_salable_before', array(
                'product' => $product
            ));

            $salable = $product->isAvailable();

            $object = new Varien_Object(array(
                'product' => $product,'is_salable' => $salable));

            Mage::dispatchEvent('catalog_product_is_salable_after', array('product' => $product,'salable' => $object));
            $_salable = $object->getIsSalable();

            // Colors
            $colors = array();
            foreach ($products as $child) {
                $product = Mage::getModel('catalog/product')->load($child->getId());


                    if(! $product->getColor() ) {
                       continue;
                    }

                    $d = array(
                        "swatch_image" => "20/".$product->getSwatchImage(),
                        "swatch_image_label" => $product->getSwatchImageLabel(),
                        "full_image" => $product->getImage(),
                    );
                    $colorId = $product->getColor();

                    if(! isset($colors[$colorId])) {
                        $colors[$colorId] = $d;
                    }

            }


            // Data query
            $query .= ($query ? ', ' : '') . '(?, ?, ?, ?)';
            array_push($queryData, $product->getId(), (int) $_salable, $store, serialize($colors));
        }
        if(! empty($query)) {
            $adapter->query('REPLACE INTO `'.$tableName.'` (`product_id`, `is_saleable`, `store_id`, `colors`) VALUES ' . $query, $queryData);
        }
    }

    /**
     * Retrieves option label by attribute name and value
     * stores all labels in array on first calling
     * @param $attrName
     * @param $value
     * @return mixed
     */
    public function getOptionLabel($attrName, $value){
        if (!isset($this->_options[$attrName])){
            $attribute = Mage::getModel('eav/config')->getAttribute('catalog_product', $attrName);
            $optArray = $attribute->getSource()->getAllOptions(false);
            foreach($optArray as $item){
                $this->_options[$attrName][$item['value']] = $item['label'];
            }
        }
        return $this->_options[$attrName][$value];
    }

    public function resizeImage($fileName, $width, $height = '')
    {
        $file = new Varien_Io_File();
        $imageParse = parse_url($fileName);
        if ($imageParse["scheme"]) {
            /*risize by url*/
            // $resizedURL =  $this->resizeByUrl( $imageParse,$file,$fileName, $width,$height);

            $resizedURL = $fileName;
        } else {
            $folderURL = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA);
            $imageURL = $folderURL . 'catalog' . DS . 'product' . $fileName;

            $basePath = Mage::getBaseDir(Mage_Core_Model_Store::URL_TYPE_MEDIA) . '/catalog' . DS . 'product' . $fileName;
            $newPath = Mage::getBaseDir(Mage_Core_Model_Store::URL_TYPE_MEDIA) . '/catalog' . DS . 'product' . DS . "resized" . DS . $width . '-' . $height . $fileName;
            $file->checkAndCreateFolder(str_replace(basename($newPath), '', $newPath));
            //if width empty then return original size image's URL
            if ($width != '') {
                //if image has already resized then just return URL
                if (!file_exists($newPath) && file_exists($basePath)) :
                    $file->checkAndCreateFolder(str_replace(basename($newPath), '', $newPath));
                    $imageObj = new Varien_Image($basePath);
                    $imageObj->constrainOnly(TRUE);
                    $imageObj->keepAspectRatio(TRUE);
                    $imageObj->keepFrame(FALSE);
                    $imageObj->resize($width, $height);
                    $imageObj->save($newPath);
                endif;
                $resizedURL = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . 'catalog' . DS . 'product' . DS . "resized" . DS . $width . '-' . $height . $fileName;
            } else {
                $resizedURL = $imageURL;
            }

        }
        return $resizedURL;
    }

}
