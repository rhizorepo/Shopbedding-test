<?php
class Shopbedding_Collection_Block_Adminhtml_Catalog_Helper_Form_Gallery_Content extends Mage_Adminhtml_Block_Catalog_Product_Helper_Form_Gallery_Content
{

    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('collection/gallery.phtml');
    }   
}
