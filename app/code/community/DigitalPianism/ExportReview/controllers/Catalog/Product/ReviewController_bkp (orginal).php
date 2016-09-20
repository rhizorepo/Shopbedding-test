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
		$fileName   = 'reviews.csv';
        $grid       = $this->getLayout()->createBlock('adminhtml/review_grid');
        $this->_prepareDownloadResponse($fileName, $grid->getCsvFile());
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
