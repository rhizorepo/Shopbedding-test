<?php 
 /************************************************************************
© 2014 Dydacomp Development Corporation.   All rights reserved.
DYDACOMP, FREESTYLE COMMERCE, and all related logos and designs are 
trademarks of Dydacomp or its affiliates. 
All other product and company names mentioned herein are used for 
identification purposes only, and may be trademarks of 
their respective companies.  
************************************************************************/
class Channelbrain_BizSyncXL_Model_Api extends Mage_Api_Model_Resource_Abstract
{
	/**
	 * getHelper function 
	 *
	 * @return void
	 * @author Paul Quirnbach
	 **/
    public function getHelper(){
	
		//return Mage::Helper('bizsyncxl');
	}
	
	/**
	 * syncData function
	 * Saves the data sent up from bizsyncxl into a "work queue" in the media/import folder
	 *
	 * @return string
	 * @author Paul Quirnbach
	 **/
	public function syncData($parametrs)
    {
		//Mage::Log(__METHOD__, Zend_Log::INFO, "bizsyncxl.log");
		$default_group_id = 'all';
		$ret_msg = "";
		
		$time1 = microtime(true);
					
		//$seconds_between_1900_1970 = 2208988800.0 ;
		
		try{
		
			// start work queue
			
			// see if magento has media/import folder.
			// If not, use the media folder.
			$base_media_dir = Mage::getBaseDir('media') . "/import";
			if (!is_dir($base_media_dir)) 
			{
				$base_media_dir = Mage::getBaseDir('media');
			}
			
			// create unique filetime by using microtime and rand
			$filename = $base_media_dir . "/" . $time1 . "_" . mt_rand( ) . "_data.bzq";
			
			// save file in queue folder
			file_put_contents( $filename, $parametrs);
			
			$time2 = microtime(true);
			$ret_msg .= " time=" . ($time2 - $time1);
				
			return $ret_msg;
			//end work queue
			
			
			
		} catch (Exception $e){
			
			Mage::Log(sprintf('Line %s in file %s',__LINE__, __FILE__) . " " . $e->getMessage(), Zend_Log::ERR, "bizsyncxl.log");
			//$this->_fault('data_invalid', 'Wrong Parameters Format');
			return $e->getMessage();
		}
		
	
		
		
		
		
		$callResult = 1;
		return $callResult;
		
    }
	
	

}