<?php

/*
Controller to add the CSV and XML export for the reviews */

include_once("Mage/Adminhtml/controllers/Catalog/Product/ReviewController.php");

class DigitalPianism_ExportReview_Catalog_Product_ReviewController extends Mage_Adminhtml_Catalog_Product_ReviewController
{	
	 /**
     * Export order grid to CSV format
     */
    public function exportCsvAction()
    {
		Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID); 
		$resource = Mage::getSingleton('core/resource');
		$readConnection = $resource->getConnection('core_read');
		
		$created_at = $this->getRequest()->getParam('created_at');
	    
		if($created_at['to']){
		$date_from=date("Y-m-d 00:00:00",strtotime($created_at['from']));
		}
		if($created_at['to']){
		$date_to=date("Y-m-d 23:59:59",strtotime($created_at['to']));
		}
		if($date_from && $date_to){
			$query="SELECT revd.email, revd.nickname FROM review_detail revd JOIN review rev ON revd.review_id = rev.review_id WHERE rev.created_at >='".$date_from."' AND  rev.created_at <= '".$date_to."' AND email != '' ";
		}else{
			$query="SELECT revd.email, revd.nickname FROM review_detail revd JOIN review rev ON revd.review_id = rev.review_id WHERE revd.email != ''";
		}
	
		$filename_enc = md5(time());
		$file = fopen(Mage::getBaseDir('base')."/var/export/".$filename_enc.".csv","w");
		$email_ids = array();
		fputcsv($file,explode(',',"Email Ids, Name"));
		foreach ($readConnection->fetchAll($query) as $rows) {
			//$email_ids = ;
			fputcsv($file,array($rows['email'],$rows['nickname']));
		}
		fclose($file);

		$file_array =  array("type" => "filename", "value" => Mage::getBaseDir('base')."/var/export/".$filename_enc.".csv" , "rm" => 1);		
        $fileName   = 'reviews.csv';
        //$grid       = $this->getLayout()->createBlock('adminhtml/review_grid');
        $this->_prepareDownloadResponse($fileName,  $file_array);
    }
	
	/**
     *  Export order grid to Excel XML format
     */
    public function exportExcelAction()
    {
        $fileName   = 'reviews.xml';
        $grid       = $this->getLayout()->createBlock('adminhtml/review_grid');
        $this->_prepareDownloadResponse($fileName, $grid->getExcelFile($fileName));
    }
}
