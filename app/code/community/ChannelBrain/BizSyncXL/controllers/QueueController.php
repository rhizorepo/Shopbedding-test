<?php 
 /************************************************************************
© 2014 Dydacomp Development Corporation.   All rights reserved.
DYDACOMP, FREESTYLE COMMERCE, and all related logos and designs are 
trademarks of Dydacomp or its affiliates. 
All other product and company names mentioned herein are used for 
identification purposes only, and may be trademarks of 
their respective companies.  
************************************************************************/
class ChannelBrain_BizSyncXL_QueueController extends Mage_Core_Controller_Varien_Action 
{

	/**
	 * processAction function
	 *
	 * @return void
	 * @author Paul Quirnbach
	 **/
	public function processAction()
	{
		//Mage::Log(__METHOD__, Zend_Log::INFO, "bizsyncxl.log");
		
		// look in the queue to see if there are any files to process
		$dirname = Mage::getBaseDir('media') . "/import";
		if (!is_dir($dirname)) 
		{
			$dirname = Mage::getBaseDir('media');
		}

		if( !isset($dirname) )
		{
		  exit();
		}
	
	
		if ($handle = opendir( $dirname)) {
		  //echo "Directory handle: $handle\n";
		  echo "Files:\n";

		  $i = 0;
		  $filelist = array();
		  /* This is the correct way to loop over the directory. */
		  while (false !== ($file = readdir($handle))) {
			//    echo "$file<br>";
			if( $file != ".." && $file != "" && $file != ".")
			  {
				// we only want files that end in .bzq , that is the extension used when saving the data
				if( strpos($file, ".bzq") )
				{
					$filelist[$i] = $file;
					$i++;
				}
			  }
		  }


		  closedir($handle);
		
		  $start = time();
		  

		  // set the number of files to process on this page request
		  $stop = ($i - 20 > 0) ? $i - 20 : 0;
		  //  $stop = 0;
		  $total = 0;
		  
		  for($j=$i-1; $j>=$stop; $j--)
			{
			  print($filelist[$j] . "<br>");
			  if ( $this->do_work( $dirname, $filelist[$j] ) === false)
				{
					// throw and exception.
					print ("ERROR: " . $filelist[$j] . "\n");
					Mage::Log(__METHOD__ . " exception in " . $filelist[$j], Zend_Log::INFO, "bizsyncxl.log");
					$exception_filename = $dirname . "/exceptions/" .  $filelist[$j];
					rename($dirname . "/" . $filelist[$j], $exception_filename);
				} else {
			        unlink( $dirname . "/" . $filelist[$j] );
				}
			  $total++;
			}
		  $end = time();
		  print("Files Processed: " . ($total) . "\n");
		  print("Elapsed Time   : " . ($end - $start) . "\n");
		}
	}
	
	/**
	 * do_work function
	 *
	 * @return bool
	 * @author Paul Quirnbach
	 **/
	public function do_work( $dirname, $filename )
	{
		$ret = true;
		try
		{
			// read the file 
			$work = file_get_contents($dirname . "/" . $filename);
			
			// split on the delimiter
			$aJSON = explode("|bz|", $work);
			
			$bzProduct = Mage::getModel('bizsyncxl/product');
			
			// process each chunk of json
			foreach ($aJSON as $json )
			{
				$ret2 = $bzProduct->ConsumeJSON($json);
				if( "OK" !=  $ret2)
				{
					Mage::Log(sprintf('Line %s in file %s',__LINE__, __FILE__) . " " . $ret2, Zend_Log::ERR, "bizsyncxl.log");
					$ret = false;
				}
			}
		
		} catch (Exception $e){
			
			Mage::Log(sprintf('Line %s in file %s',__LINE__, __FILE__) . " " . $e->getMessage(), Zend_Log::ERR, "bizsyncxl.log");
			
			return false;
		}
		
		return $ret;
	}
	
	
}