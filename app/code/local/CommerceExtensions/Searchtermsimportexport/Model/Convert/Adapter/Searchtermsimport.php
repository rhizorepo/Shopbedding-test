<?php
/**
 * Searchtermsimport.php
 * CommerceExtensions @ InterSEC Solutions LLC.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.commerceextensions.com/LICENSE-M1.txt
 *
 * @category   SearchTerms
 * @package    Searchtermsimport
 * @copyright  Copyright (c) 2003-2010 CommerceExtensions @ InterSEC Solutions LLC. (http://www.commerceextensions.com)
 * @license    http://www.commerceextensions.com/LICENSE-M1.txt
 */ 
 
class CommerceExtensions_Searchtermsimportexport_Model_Convert_Adapter_Searchtermsimport extends Mage_Eav_Model_Convert_Adapter_Entity
{
    protected $_stores;

    public function parse()
    {
        $batchModel = Mage::getSingleton('dataflow/batch');
        /* @var $batchModel Mage_Dataflow_Model_Batch */

        $batchImportModel = $batchModel->getBatchImportModel();
        $importIds = $batchImportModel->getIdCollection();

        foreach ($importIds as $importId) {
            //print '<pre>'.memory_get_usage().'</pre>';
            $batchImportModel->load($importId);
            $importData = $batchImportModel->getBatchData();

            $this->saveRow($importData);
        }
    }

    /**
     * Save Customer Review (import)
     *
     * @param array $importData
     * @throws Mage_Core_Exception
     * @return bool
     */
    public function saveRow(array $importData)
    {
	
		 $resource = Mage::getSingleton('core/resource');
		 $prefix = Mage::getConfig()->getNode('global/resources/db/table_prefix'); 
		 $write = $resource->getConnection('core_write');
		 $read = $resource->getConnection('core_read');
		 $wishlist =  Mage::getModel('wishlist/wishlist');
		
		if (empty($importData['store_id'])) {	
			$message = Mage::helper('catalog')->__('Skip import row, required field "%s" not defined', 'sku');
			Mage::throwException($message);
		} else {	
            $store = $this->getStoreById($importData['store_id']);
			#$store = Mage::app()->getStore('default');
		}
		 //CONVERT DATE TIME TO MYSQL DATE TIME
		 if(isset($importData['updated_at']) && $importData['updated_at'] !="") {
			$UpdatedDateTime = strtotime($importData['updated_at']);	
		 } else {
			$UpdatedDateTime = strtotime(date("m/d/Y, g:i a"));	
		 }
		
		$query_text = htmlspecialchars_decode($importData['query_text']);
		$resource = Mage::getSingleton('core/resource');
		$prefix = Mage::getConfig()->getNode('global/resources/db/table_prefix'); 
		$read = $resource->getConnection('core_read');
		
		$select_qry = $read->query("SELECT query_id FROM `".$prefix."catalogsearch_query` WHERE query_text = \"".addslashes($query_text)."\" AND store_id = '".$importData['store_id']."'");
		$newrowItemId = $select_qry->fetch();
		$query_id = $newrowItemId['query_id'];
		if($query_id != "" && $query_id > 0) {
			 // DELETE OR UPDATES SEARCH TERM DATA
			 if($this->getBatchParams('delete_searchterms_on_match') == "true") {
				$write->query("DELETE FROM `".$prefix."catalogsearch_query` WHERE query_id = ". $query_id ."");
			} else {
				$write->query("UPDATE `".$prefix."catalogsearch_query` SET query_text = \"".addslashes($query_text)."\", num_results = '".$importData['num_results']."', popularity = '".$importData['popularity']."', redirect = '".$importData['redirect']."', synonym_for = '".$importData['synonym_for']."', store_id = '".$importData['store_id']."', display_in_terms = '".$importData['display_in_terms']."', is_active = '".$importData['is_active']."', is_processed = '".$importData['is_processed']."', updated_at = '".date("Y-m-d H:i:s", $UpdatedDateTime)."' WHERE query_id = ". $query_id ."");
			
			}
			
		} else {
			 // INSERTS SEARCH TERM DATA
			 $write->query("Insert INTO `".$prefix."catalogsearch_query` (query_text,num_results,popularity,redirect,synonym_for,store_id,display_in_terms,is_active,is_processed,updated_at) VALUES (\"".addslashes($query_text)."\",'".$importData['num_results']."','".$importData['popularity']."','".$importData['redirect']."','".$importData['synonym_for']."','".$importData['store_id']."','".$importData['display_in_terms']."','".$importData['is_active']."','".$importData['is_processed']."','".date("Y-m-d H:i:s", $UpdatedDateTime)."')");
		}

		
        return true;
    }

    /**
     * Retrieve store object by code
     *
     * @param string $store
     * @return Mage_Core_Model_Store
     */
    public function getStoreByCode($store)
    {
        $this->_initStores();
        if (isset($this->_stores[$store])) {
            return $this->_stores[$store];
        }
        return false;
    }

    /**
     *  Init stores
     *
     *  @param    none
     *  @return      void
     */
    protected function _initStores ()
    {
        if (is_null($this->_stores)) {
            $this->_stores = Mage::app()->getStores(true, true);
            foreach ($this->_stores as $code => $store) {
                $this->_storesIdCode[$store->getId()] = $code;
            }
        }
    }
	
	protected function getStoreById($id)
   {
       $this->_initStores();
       /**
        * In single store mode all data should be saved as default
        */
       if (Mage::app()->isSingleStoreMode()) {
           return Mage::app()->getStore(Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID);
       }

       if (isset($this->_storesIdCode[$id])) {
           return $this->getStoreByCode($this->_storesIdCode[$id]);
       }
       return false;
   }

}

?>