<?php
class HunterGroupCo_ConfigurableIndex_Model_Saleableindexer extends Mage_Index_Model_Indexer_Abstract
{
    protected $_resourceModel = null;

    protected $_matchedEntities = array(
        Mage_Catalog_Model_Product::ENTITY => array(
            Mage_Index_Model_Event::TYPE_SAVE
        ),
    );

    public function getName(){
        return Mage::helper('huntergroupco_configurableindex')->__('Product availability');
    }

    public function getDescription(){
        return Mage::helper('huntergroupco_configurableindex')->__('Reindex configurable product availability');
    }

    protected function getResourceModel(){
        if($this->_resourceModel == null ) {
            $this->_resourceModel = Mage::getResourceSingleton('huntergroupco_configurableindex/saleable');
        }

        return $this->_resourceModel;
    }

    /**
     * Register data required by process in event object
     *
     * @param Mage_Index_Model_Event $event
     */
    protected function _registerEvent(Mage_Index_Model_Event $event)
    {
        $product = $event->getDataObject();
        $event->addNewData('reindex_configurable_saleable_ids', array($product->getId()));
        return $this;
    }

    protected function _processEvent(Mage_Index_Model_Event $event){
        $data = $event->getNewData();
        $resourceModel = $this->getResourceModel();

        if(isset($data['reindex_configurable_saleable_ids'])){
            $resourceModel->refreshSaleableData($data['reindex_configurable_saleable_ids']);
        }

        return $this;
        // process index event
    }

    /**
     * Reindexing booking index for all applicable products
     *
     * @throws Exception
     * @return void
     */
    public function reindexAll(){

        /** @var $resourceModel Mage_Catalog_Model_Resource_Url */
        $resourceModel = $this->getResourceModel();
        $resourceModel->beginTransaction();

    //    try {
            $resourceModel->refreshAllIndexes();
            $resourceModel->commit();
      /*  } catch (Exception $e) {
            $resourceModel->rollBack();
            throw $e;
        }*/
    }
}
