<?php
/**
 * Searchtermsexport.php
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
 * @package    Searchtermsexport
 * @copyright  Copyright (c) 2003-2010 CommerceExtensions @ InterSEC Solutions LLC. (http://www.commerceextensions.com)
 * @license    http://www.commerceextensions.com/LICENSE-M1.txt
 */ 
 
class CommerceExtensions_Searchtermsimportexport_Model_Convert_Parser_Searchtermsexport extends Mage_Eav_Model_Convert_Parser_Abstract
{
/**
     * @deprecated not used anymore
     */
    public function parse()
    {
			return $this;
		}
 /**
     * Unparse (prepare data) loaded categories
     *
     * @return Mage_Catalog_Model_Convert_Adapter_Searchtermsexport
     */
    public function unparse()
    {
				 $recordlimitstart = $this->getVar('searchterms_recordlimitstart');
				 $recordlimitend = $this->getVar('searchterms_recordlimitend') - $this->getVar('searchterms_recordlimitstart');
				
				 $resource = Mage::getSingleton('core/resource');
				 $prefix = Mage::getConfig()->getNode('global/resources/db/table_prefix');
				 $read = $resource->getConnection('core_read');
				 $row = array();
				 
				 $select_qry = "SELECT query_id, query_text, num_results, popularity, redirect, synonym_for, store_id, display_in_terms, is_active, is_processed, updated_at FROM ".$prefix."catalogsearch_query ORDER BY query_text ASC LIMIT ".$recordlimitstart.",".$recordlimitend;
				
				 $rows = $read->fetchAll($select_qry);
				 foreach($rows as $data)
				 { 
						$row["query_text"] = htmlspecialchars($data['query_text']);
						$row["num_results"] = $data['num_results'];
						$row["popularity"] = $data['popularity'];
						$row["redirect"] = $data['redirect'];
						$row["synonym_for"] = $data['synonym_for'];
						$row["store_id"] = $data['store_id'];
						$row["display_in_terms"] = $data['display_in_terms'];
						$row["is_active"] = $data['is_active'];
						$row["is_processed"] = $data['is_processed'];
						$row["updated_at"] = $data['updated_at'];
								
								
						$batchExport = $this->getBatchExportModel()
						->setId(null)
						->setBatchId($this->getBatchModel()->getId())
						->setBatchData($row)
						->setStatus(1)
						->save();
				 }
					 
					 
        return $this;
		}
}

?>