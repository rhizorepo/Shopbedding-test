<?php
class CompleteWeb_ResizeSwatch_Model_Cron{	
	public function resizeSwatch(){
		$swatch_path = Mage::getBaseDir('media')."/swatchIcons/";
		foreach(scandir($swatch_path ,1) as $images){
			$sourcePathFull = $swatch_path . $images ; 
			$resize20PathFull = $swatch_path . "/20/" . $images ; 
			$resize30PathFull = $swatch_path . "/30/" . $images ; 
			$resize35PathFull = $swatch_path . "/35/" . $images ; 
			if (file_exists($sourcePathFull)) {
				if( is_file($sourcePathFull) ) {
					try {
						$imageObj = new Varien_Image($sourcePathFull);
						//$imageObj->constrainOnly(TRUE);
						$imageObj->keepAspectRatio(TRUE);
						if(!file_exists($resize20PathFull)) {
							$imageObj->resize(20,20);
							$imageObj->save($resize20PathFull);				
						}
						if(!file_exists($resize30PathFull)) {
							$imageObj->resize(30,30);
							$imageObj->save($resize30PathFull);
						}
						if(!file_exists($resize35PathFull)) {
							$imageObj->resize(35,35);
							$imageObj->save($resize35PathFull);
						}
					} catch (Exception $e) { 
						//Mage::log($e, null, 'resize_error.log');
					}
				}
			}
		}
	} 
}
