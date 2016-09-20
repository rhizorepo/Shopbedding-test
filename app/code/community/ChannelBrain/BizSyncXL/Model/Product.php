<?php
/* * **********************************************************************
  © 2014 Dydacomp Development Corporation.   All rights reserved.
  DYDACOMP, FREESTYLE COMMERCE, and all related logos and designs are
  trademarks of Dydacomp or its affiliates.
  All other product and company names mentioned herein are used for
  identification purposes only, and may be trademarks of
  their respective companies.
 * ********************************************************************** */
function escapeInventoryFileData($str_data) 
{
	// 2012 june 27 - PJQ - For Hyperdrug/justpetfood
	$str_data = str_replace("amp;", "", $str_data);

	$searched_data = array("\r\n", "\n", "\r", "\t", "\\");
	$replaced_data = ' ';
	return str_replace($searched_data, $replaced_data, $str_data);
}

class ChannelBrain_BizSyncXL_Model_Product extends Mage_Core_Model_Abstract {

    public function _construct() {
        parent::_construct();
        $this->_init('bizsyncxl/product');
    }
	
	/**
	 * ConsumeJSON function
	 *
	 * @return string  "OK" on success, otherwise error message
	 * @author Paul Quirnbach
	 **/
	public function ConsumeJSON( $json )
	{
		//Mage::Log(__METHOD__, Zend_Log::INFO, "bizsyncxl.log");
		
		$parametrsArray = (array)json_decode($json);
		
		$row = $parametrsArray[0];
		
		if( $row[0] != "prices" && $row[0] != "quantity" && $row[0] != "product")
		{
			$ret_msg = __METHOD__ . " Invalid Data - action must be product , prices or quantity";
			Mage::Log($ret_msg, Zend_Log::ERR, "bizsyncxl.log");
			return $ret_msg;
		}
		
		$sku = $row[1];
		
		if( $sku == "" )
		{
			$ret_msg = __METHOD__ . " SKU Empty";
			Mage::Log($ret_msg, Zend_Log::ERR, "bizsyncxl.log");
			return $ret_msg;
		}
		
		if( $row[0] == "prices" )
		{
			//return $this->SyncPrices_ctype($parametrsArray);
			return $this->SyncPrices($parametrsArray);
			
		}
		
		if( $row[0] == "quantity" )
		{
			return $this->SyncQuantity($parametrsArray);
			
		}
		
		if( $row[0] == "product" )
		{
			return $this->SyncProduct($parametrsArray);
			
		}
		
		return "OK";
	}
	
	/**
	 * SyncPrices_ctype function
	 *
	 * @return string  "OK" on success, otherwise error message
	 * @author Paul Quirnbach
	 **/
	public function SyncPrices_ctype($parametrsArray) 
	{	
		$CustomerType = "ctype";  // ctype, ctype2, ctype3
		$default_group_id = 'all';

		$row = $parametrsArray[0];
		$sku = $row[1];		
		$price1 = $row[2];
		$store_id = (count($row) > 3 && is_numeric($row[3])) ? $row[3] : 1;
		
		
		
		if( !is_numeric($price1) )
		{
			$ret_msg = "Normal Selling Price cannot be empty";
			Mage::Log($ret_msg, Zend_Log::ERR, "bizsyncxl.log");
			return $ret_msg;
		}
		
		
		
		Mage::app()->setCurrentStore(Mage::getModel('core/store')->load(Mage_Core_Model_App::ADMIN_STORE_ID));		
		$prod = Mage::getModel('catalog/product');
		$productId = $prod->getIdBySku($sku);
		
		if( !is_numeric($productId) )
		{
			$ret_msg = "SKU " . $sku . " not found in Magento catalog";
			Mage::Log($ret_msg, Zend_Log::INFO, "bizsyncxl.log");
			return $ret_msg;
		}
		
		if( intval($productId) > 0 )
		{
			//$prod_api =  new Mage_Catalog_Model_Product_Api();
			// set the price to the 'normal retail selling price' in MOM.
			$product['price'] = $price1;
			
			$prod->load($productId);
			if ($prod && $prod->getId()) 
			{
				//$prod->setStoreId(intval($store_id));
				$prod->setPrice($price1);
				
				
			}
			$ret_msg = "SKU " . $sku . " Normal Selling Price Updated." ;
			
			$tierprices = array();
			$groupprices = array();
			$updated_special_price = 0;
			
			foreach($parametrsArray as $row)
			{
				$product2 = array();
				
				// skip first row, we already processed it
				if( $row[0] == "prices")
				{
					continue;
				}
				
				//"select rtrim(NUMBER), NPOSITION, PRICE, DISCOUNT, COSTPLUS, QTY, rtrim(CL_KEY), TOTAL_DOL, ";
				//rtrim(CATCODE), rtrim(CTYPE), rtrim(CTYPE2), rtrim(CTYPE3),  FORMAT(DATE_START,'u'),  FORMAT(DATE_END.'u'), CUSTNUM, RFM, rtrim(ORDERTYPE), 
				//rtrim(COSTMETHOD), rtrim(PLEVEL), PRICE_ID ";
				$stocknumber = $row[0];
				if($stocknumber != $sku)
				{
					continue;
				}
				$npos  = $row[1];
				$price  = $row[2];
				$discount  = $row[3];
				$costplus  = $row[4];
				$qty  = $row[5];
				$coupon  = $row[6];
				$total_dol  = $row[7];
				$catcode  = $row[8];
				$ctype  = $row[9];
				$ctype2  = $row[10];
				$ctype3  = $row[11];
				$from_date  = $row[12];
				$to_date  = $row[13];
				$custnum  = $row[14];
				$rfm  = $row[15];
				$ordertype  = $row[16];
				$costmethod  = $row[17];
				$plevel  = $row[18];
				$price_id  = $row[19];
				
				// check modifiers
				// for 1.1.x we do not support discounts coming from MOM that have Ctype's or other exclusions (i.e. date ranges, catalog codes)
				// We need to make sure that we don't add them if we have any on the price line coming in that have these modifiers.
				// for 2.0, this is not going to be the case.
				$has_price_modifier = false;
				
				if ($ctype != '' 		||
					$ctype2 != ''		||
					$ctype3 != ''		||
					$coupon != ''		||
					$catcode != ''		||
					$total_dol != ''	||
					$rfm  != ''			||
					$ordertype != ''	||
					$custnum != '')
				{
					$has_price_modifier = true;
					// comment out the continue to do customer types and other modifiers
					//continue;
				}
				
				// 2014-08-14 PJQ - plevel W (web) and B (both) are good, along with empty value
				// plevel N (non web orders) is not good
				if ($plevel == 'N')
				{
					continue;
				}
				
				// gym: 08/16/11 - make sure we do percentage off discounts.
				// convert to a regular price.
				if($discount > 0)
				{
					// BSXL-113 2014-08-18 PJQ - if price record has a price, discount from that otherwise discount from price1
					if( $price > 0 )
					{
						$price = $price * (1 - ($discount / 100));
					}
					else
					{
						if( $price1 > 0 )
						{
							$price = $price1 * (1 - ($discount / 100));
						}
					}
				}
				
				//if its qty 1, then it needs to go into magento "special price" as magento tier prices must be qty > 1
				if($qty == '1' && !$has_price_modifier)
				{
				
					if (floatval ($price) < floatval ($price1) && floatval($price) > 0 )
					{
						
						//$special_price = $price;
						$product2['special_price'] = $price;
						if ($from_date != '')
						{
							$product2['special_from_date'] = substr($from_date, 0, 10);
						} else {
							$product2['special_from_date'] = '';
						}
						if ($to_date != '')
						{
							$product2['special_to_date'] = substr($to_date, 0, 10);
						} else {
							$product2['special_to_date'] = '';
						}	

						
					} else {
						// clear the special price.
						$product2['special_from_date'] = '';
						$product2['special_to_date'] = '';
						$product2['special_price'] = '';
					}
					
					// 2013dec17 PJQ - add 4th parameter identifierType to call
					//$prod_api->update($productId, $product2, intval($store_id), "id");
					//$prod->load($productId);
					
					if ($prod && $prod->getId()) 
					{
						//$prod->setStoreId(intval($store_id));
						$prod->setSpecialFromDate($product2['special_from_date']);
						$prod->setSpecialFromDateIsFormated(true);
						$prod->setSpecialToDate($product2['special_to_date']);
						$prod->setSpecialToDateIsFormated(true);
						$prod->setSpecialPrice($product2['special_price']);
						//$prod->save();
					}
					
					$ret_msg .= " Set special price." ;
					$updated_special_price = 1;
					
					continue;
				
				} // END magento "special price"
				
			
				// make sure we don't have a price modifier that is not supported
				$has_unsupported_price_modifier = false;
				
				if (//$ctype != '' 		||
					//$ctype2 != ''		||
					//$ctype3 != ''		||
					$coupon != ''		||
					$catcode != ''		||
					$total_dol != ''	||
					$rfm  != ''			||
					$ordertype != ''	||
					$plevel  != ''		||
					$custnum != '')
				{
					$has_unsupported_price_modifier = true;
					
					continue;
				}
				
				// figure out the group ID
				$group_id = $this->GetGroupID($ctype, $ctype2, $ctype3, $CustomerType, $default_group_id);
				
				if( $qty == 1 )
				{
					Mage::log("Customer " . $ctype . " price " . $price . " for SKU " . $sku, Zend_Log::INFO, "bizsyncxl.log");
					$groupprice['cust_group'] = $group_id;
					$groupprice['website_id'] = 'all';
					$groupprice['price'] = $price;
					
					$groupprices[] = $groupprice;
				}
				else
				{
					$tierprice['customer_group_id'] = $group_id;
					$tierprice['website_id'] = 'all';
					$tierprice['qty'] = $qty;
					$tierprice['price'] = $price;
					$tierprice['from_date'] = $from_date;
					$tierprice['to_date'] = $to_date;
				
				
				
				
					// Tier Pricing
					$tierprices[] = $tierprice;
									//array('website'   => 'all',
								  //'customer_group_id' => $default_group_id,
								  //'qty'               => $qty,
								  //'price'             => $price);
				}
			}
			
			// do the actually save of the regular price and special price (if there was one)
			if ($prod && $prod->getId()) 
			{
				$prod->save();
			}

			
			// save the tierprices
			if( count($tierprices) > 0)
			{
				$mcmpata = null;
				//$mcmpata = new Mage_Catalog_Model_Product_Attribute_Tierprice_Api();
				if (Mage::helper('core')->isModuleEnabled(ChannelBrain_Tierpricedates))
				{
					$mcmpata = new ChannelBrain_Tierpricedates_Model_Product_Attribute_Tierprice_Api();
					Mage::log("ChannelBrain_Tierpricedates module being used.", Zend_Log::INFO, "bizsyncxl.log");
					$ChannelBrain_Tierpricedates = true;
				} else {
					// Use Mage core.
					$mcmpata = new Mage_Catalog_Model_Product_Attribute_Tierprice_Api();
				}	
				// Replace tier prices in magento
				if( is_object( $mcmpata ) )
				{
					if ($mcmpata->update($productId, $tierprices, "id")) {
						$ret_msg .= " Tier Prices Updated.";
					} else {
						$ret_msg .= " Tier Prices failed to update.";
					}
				}
			}
			
			if( !$updated_special_price )
			{
				// clear the special price.
				$prod_api =  new Mage_Catalog_Model_Product_Api();
				$product2['special_from_date'] = '';
				$product2['special_to_date'] = '';
				$product2['special_price'] = '';
				$prod_api->update($productId, $product2, intval($store_id), "id");
				$ret_msg .= " Clear special price.";
			}
			
			// save group prices
			if( count($groupprices) > 0 )
			{
				// clear the special price.
				$prod_api =  new Mage_Catalog_Model_Product_Api();
				$product3['group_price'] = $groupprices;
				$prod_api->update($productId, $product3, intval($store_id), "id");
				$ret_msg .= " Set Customer Group price.";
			}
			$time2 = microtime(true);
			$ret_msg .= " time=" . ($time2 - $time1);
			Mage::Log($ret_msg, Zend_Log::INFO, "bizsyncxl.log");
			
			return "OK";				
		}
		
		
		//Mage::Log(sprintf('Line %s in file %s',__LINE__, __FILE__));
		return "OK";
				
	}
	
	public function GetGroupID($ctype, $ctype2, $ctype3, $CustomerType, $default_group_id)
	{
		// initialize it to the default and if we don't find anything we will return that
		$group_id = $default_group_id;
		
		$ctypes = array();
		$ctypes['ctype'] = $ctype;
		$ctypes['ctype2'] = $ctype2;
		$ctypes['ctype3'] = $ctype3;
		
		// for Magento we can only have ONE Ctype (group) for a price tier.
		// let's make sure we set that now.  This is globally set in the bizsync.globals.inc.php OR PASSED IN TO THIS FUNCTION
		$CTypeForGroup = $ctypes[$CustomerType];
		try
		{
			if ($CTypeForGroup != '')
			{
				// let's go get the actual group code object.
				$customer_discount_group = Mage::getModel('customer/group')
												->getCollection()
												->addFieldToFilter('customer_group_code', array('eq' => $CTypeForGroup))
												->getFirstItem();

				if (is_object ($customer_discount_group))
				{
						$group_id = $customer_discount_group->getId();
						// let's make sure we have a group id.
						if (strval($group_id) == '')
						{
							// no group id found, was it originally created?!
							$ret_msg =  " price group value not found for '" . $CTypeForGroup . "'. Group needs to be created first under 'Customer Groups' for customers.";
							Mage::Log($ret_msg, Zend_Log::INFO, "bizsyncxl.log");
						}
				} else {
					// no group id found, was it originally created?!
					$ret_msg = $sku . " price group value not set for '" . $CTypeForGroup . "'. This group needs to be created first under 'Customer Groups' for customers.";
					//Ctype: '" . $ctype . "', ctype2: '" . $ctype2 . "', ctype3: '" . $ctype3 . "', coupon: '" . $coupon . "', catalog: '" . $catalog . "', custnum: '" . $custnum . "'");
					Mage::Log($ret_msg, Zend_Log::INFO, "bizsyncxl.log");
				}
			} 
		} catch (Exception $e) {
			$ret_msg = "Customer Group: Exception caught on line " . __LINE__ . " in " . __FUNCTION__ . " with error '" . $e->GetMessage() . "' for '" . $sku . "'";
			Mage::Log($ret_msg, Zend_Log::INFO, "bizsyncxl.log");
		}
		
		return $group_id;
	}
	
	/**
	 * SyncPrices function
	 *
	 * @return string  "OK" on success, otherwise error message
	 * @author Paul Quirnbach
	 **/
	public function SyncPrices($parametrsArray) 
	{	
		$row = $parametrsArray[0];
		$sku = $row[1];		
		$price1 = $row[2];
		$store_id = (count($row) > 3 && is_numeric($row[3])) ? $row[3] : 1;
		
		
		
		if( !is_numeric($price1) )
		{
			$ret_msg = "Normal Selling Price cannot be empty";
			Mage::Log($ret_msg, Zend_Log::ERR, "bizsyncxl.log");
			return $ret_msg;
		}
		
		
		
		Mage::app()->setCurrentStore(Mage::getModel('core/store')->load(Mage_Core_Model_App::ADMIN_STORE_ID));		
		$prod = Mage::getModel('catalog/product');
		$productId = $prod->getIdBySku($sku);
		
		if( !is_numeric($productId) )
		{
			$ret_msg = "SKU " . $sku . " not found in Magento catalog";
			Mage::Log($ret_msg, Zend_Log::INFO, "bizsyncxl.log");
			return $ret_msg;
		}
		
		if( intval($productId) > 0 )
		{
			//$prod_api =  new Mage_Catalog_Model_Product_Api();
			// set the price to the 'normal retail selling price' in MOM.
			$product['price'] = $price1;
			
			// 2013dec17 PJQ - add 4th parameter identifierType to call
			//$prod_api->update($productId, $product, intval($store_id), "id");	
			$prod->load($productId);
			if ($prod && $prod->getId()) 
			{
				//$prod->setStoreId(intval($store_id));
				$prod->setPrice($price1);
				
				
			}
			$ret_msg = "SKU " . $sku . " Normal Selling Price Updated." ;
			
			$tierprices = array();
			$updated_special_price = 0;
			
			foreach($parametrsArray as $row)
			{
				$product2 = array();
				
				// skip first row, we already processed it
				if( $row[0] == "prices")
				{
					continue;
				}
				
				//"select rtrim(NUMBER), NPOSITION, PRICE, DISCOUNT, COSTPLUS, QTY, rtrim(CL_KEY), TOTAL_DOL, ";
				//rtrim(CATCODE), rtrim(CTYPE), rtrim(CTYPE2), rtrim(CTYPE3),  FORMAT(DATE_START,'u'),  FORMAT(DATE_END.'u'), CUSTNUM, RFM, rtrim(ORDERTYPE), 
				//rtrim(COSTMETHOD), rtrim(PLEVEL), PRICE_ID ";
				$stocknumber = $row[0];
				if($stocknumber != $sku)
				{
					continue;
				}
				$npos  = $row[1];
				$price  = $row[2];
				$discount  = $row[3];
				$costplus  = $row[4];
				$qty  = $row[5];
				$coupon  = $row[6];
				$total_dol  = $row[7];
				$catcode  = $row[8];
				$ctype  = $row[9];
				$ctype2  = $row[10];
				$ctype3  = $row[11];
				$from_date  = $row[12];
				$to_date  = $row[13];
				$custnum  = $row[14];
				$rfm  = $row[15];
				$ordertype  = $row[16];
				$costmethod  = $row[17];
				$plevel  = $row[18];
				$price_id  = $row[19];
				
				// check modifiers
				// for 1.1.x we do not support discounts coming from MOM that have Ctype's or other exclusions (i.e. date ranges, catalog codes)
				// We need to make sure that we don't add them if we have any on the price line coming in that have these modifiers.
				// for 2.0, this is not going to be the case.
				$has_price_modifier = false;
				
				if ($ctype != '' 		||
					$ctype2 != ''		||
					$ctype3 != ''		||
					$coupon != ''		||
					$catcode != ''		||
					$total_dol != ''	||
					$rfm  != ''			||
					$ordertype != ''	||
					$custnum != '')
				{
					$has_price_modifier = true;
					// comment out the continue to do customer types and other modifiers
					continue;
				}
				
				// 2014-08-14 PJQ - plevel W (web) and B (both) are good, along with empty value
				// plevel N (non web orders) is not good
				if ($plevel == 'N')
				{
					continue;
				}
				
				
				// gym: 08/16/11 - make sure we do percentage off discounts.
				// convert to a regular price.
				if($discount > 0)
				{
					// BSXL-113 2014-08-18 PJQ - if price record has a price, discount from that otherwise discount from price1
					if( $price > 0 )
					{
						$price = $price * (1 - ($discount / 100));
					}
					else
					{
						if( $price1 > 0 )
						{
							$price = $price1 * (1 - ($discount / 100));
						}
					}
				}
				
				//if its qty 1, then it needs to go into magento "special price" as magento tier prices must be qty > 1
				if($qty == '1' && !$has_price_modifier)
				{
				
					if (floatval ($price) < floatval ($price1) && floatval($price) > 0 )
					{
						
						//$special_price = $price;
						$product2['special_price'] = $price;
						if ($from_date != '')
						{
							$product2['special_from_date'] = substr($from_date, 0, 10);
						} else {
							$product2['special_from_date'] = '';
						}
						if ($to_date != '')
						{
							$product2['special_to_date'] = substr($to_date, 0, 10);
						} else {
							$product2['special_to_date'] = '';
						}	

						//if ($price_message != '')
						//{
						//	$price_message = $price_message . " and Special price";
						//} else {
						//	$price_message .= "Special price";
						//}
					} else {
						// clear the special price.
						$product2['special_from_date'] = '';
						$product2['special_to_date'] = '';
						$product2['special_price'] = '';
					}
					
					// 2013dec17 PJQ - add 4th parameter identifierType to call
					//$prod_api->update($productId, $product2, intval($store_id), "id");
					//$prod->load($productId);
					
					if ($prod && $prod->getId()) 
					{
						//$prod->setStoreId(intval($store_id));
						$prod->setSpecialFromDate($product2['special_from_date']);
						$prod->setSpecialFromDateIsFormated(true);
						$prod->setSpecialToDate($product2['special_to_date']);
						$prod->setSpecialToDateIsFormated(true);
						$prod->setSpecialPrice($product2['special_price']);
						//$prod->save();
					}
					
					$ret_msg .= " Set special price." ;
					$updated_special_price = 1;
					
					continue;
				
				} // END magento "special price"
				
				// Tier Pricing
				$tierprices[] = array('website'   => 'all',
							  'customer_group_id' => $default_group_id,
							  'qty'               => $qty,
							  'price'             => $price);
			}
			
			
			if ($prod && $prod->getId()) 
			{
				$prod->save();
			}
			
			
			if( count($tierprices) > 0)
			{
				$mcmpata = new Mage_Catalog_Model_Product_Attribute_Tierprice_Api();
				// Replace tier prices in magento
				if ($mcmpata->update($productId, $tierprices, "id")) {
					$ret_msg .= " Tier Prices Updated.";
				} else {
					$ret_msg .= " Tier Prices failed to update.";
				}
			}
			
			if( !$updated_special_price )
			{
				// clear the special price.
				$prod_api =  new Mage_Catalog_Model_Product_Api();
				$product2['special_from_date'] = '';
				$product2['special_to_date'] = '';
				$product2['special_price'] = '';
				$prod_api->update($productId, $product2, intval($store_id), "id");
				$ret_msg .= " Clear special price.";
			}
			
			$time2 = microtime(true);
			$ret_msg .= " time=" . ($time2 - $time1);
			Mage::Log($ret_msg, Zend_Log::INFO, "bizsyncxl.log");
			
			return "OK";				
		}
		
		
		//Mage::Log(sprintf('Line %s in file %s',__LINE__, __FILE__));
		return "OK";
				
	}
	
	/**
	 * SyncQuantity function
	 *
	 * @return string "OK" on success, otherwise error message
	 * @author Paul Quirnbach
	 **/
	public function SyncQuantity($parametrsArray) 
	{	
		$row = $parametrsArray[0];
		$sku = $row[1];		
		$qty = $row[2];
		$store_id = (count($row) > 3 && is_numeric($row[3])) ? $row[3] : 1;
		$manage_inventory = (count($row) > 4 && is_numeric($row[4])) ? $row[4] : 1;
		$deldate = (count($row) > 5 ) ? $row[5] : "";
		
		
		if( !is_numeric($qty) )
		{
			$ret_msg = "SKU " . $sku . " Quantity must be numeric";
			Mage::Log($ret_msg, Zend_Log::ERR, "bizsyncxl.log");
			return $ret_msg;
		}
		
		
		
		Mage::app()->setCurrentStore(Mage::getModel('core/store')->load(Mage_Core_Model_App::ADMIN_STORE_ID));		
		$prod = Mage::getModel('catalog/product');
		$productId = $prod->getIdBySku($sku);
		
		if( !is_numeric($productId) )
		{
			$ret_msg = "SKU " . $sku . " not found in Magento catalog";
			Mage::Log($ret_msg, Zend_Log::INFO, "bizsyncxl.log");
			return $ret_msg;
		}
		
		if( intval($productId) > 0 )
		{
			$in_stock = ($qty > 0) ? 1 : 0;
			
			$product_stock = new Mage_CatalogInventory_Model_Stock_Item_Api();
			//$product_stock->update($productId, array('qty' => $qty, 'is_in_stock' => $in_stock), intval($store_id));
			// 2013jan10 PJQ - looking at the source code for Mage_CatalogInventory_Model_Stock_Item_Api::update, it always does its own "getIdBySku"
			// and if it finds one, it changes the productId to the productId it found, 
			// so the only way to be sure to update the proper item's quantity is to pass in the SKU
			//$product_stock->update($sku, array('qty' => $qty, 'is_in_stock' => $in_stock, intval($store_id)));
			$product_stock->update($sku, array('qty' => $qty, 'is_in_stock' => $in_stock, 'use_config_manage_stock'=>$manage_inventory, 'manage_stock'=>$manage_inventory), intval($store_id));
					
			$ret_msg = "SKU " . $sku . " quantity set to " . $qty;
			Mage::Log($ret_msg, Zend_Log::INFO, "bizsyncxl.log");
			//return "OK";
		}
		
		if($deldate != "")
		{
			$ret_msg = $sku . " Delivery date: " . $deldate;
			Mage::Log($ret_msg, Zend_Log::INFO, "bizsyncxl.log");
			
			// put it in po_date ?
			//$prod_api =  new Mage_Catalog_Model_Product_Api();
			//$product['po_date'] = $deldate;
			//$prod_api->update($productId, $product, intval($store_id));
		}
		
		return "OK";
	}
	
	
	/**
	 * SyncProduct function
	 *
	 * @return string "OK" on success, otherwise error message
	 * @author Paul Quirnbach
	 **/
	public function SyncProduct($parametrsArray) 
	{	
		$row = $parametrsArray[0];
		$sku = $row[1];		
		$data = base64_decode($row[2]);
		$data = json_decode($data);
		
		$product_name = ($data[4] == "") ? $data[3] : $data[3] . " " . $data[4];
		$wgt = $data[5];
		$short_description = ($data[4] == "") ? $data[3] : $data[3] . " " . $data[4];
		$description = $data[3] . " " . $data[8];
		$price = $data[1];
		$qty = $data[2];
		
		$taxable = ($data[6]) == "1" ? 0 : 2;
		$is_active = 1;
		//print_r($data);
		//die;
		$store_id = (count($row) > 3 && is_numeric($row[3])) ? $row[3] : 1;
		//$manage_inventory = (count($row) > 4 && is_numeric($row[4])) ? $row[4] : 1;
		
		//if( !is_numeric($qty) )
		//{
		//	$ret_msg = "SKU " . $sku . " Quantity must be numeric";
		//	Mage::Log($ret_msg, Zend_Log::ERR, "bizsyncxl.log");
		//	return $ret_msg;
		//}
		
		
		
		Mage::app()->setCurrentStore(Mage::getModel('core/store')->load(Mage_Core_Model_App::ADMIN_STORE_ID));		
		$prod = Mage::getModel('catalog/product');
		$productId = $prod->getIdBySku($sku);
		
		
		
		if( intval($productId) > 0 )
		{
			// update
		}
		else
		{
			// map channelbrain to Magento.
			$omx2mage_product_data_map = array(
				'name'              => escapeInventoryFileData($product_name),
				'weight'	    	=> $wgt ? $wgt : 1,
				'short_description' => escapeInventoryFileData($short_description),		
				'description'       => escapeInventoryFileData($description),
				'price'             =>  $price,
				'tax_class_id'		=> $taxable,
				'status'	=> $is_active
			);
			
			$prod_type = "simple";
			$default_attribute_set_id = 4;
			$itemcode =$sku;
			$prod_api =  new Mage_Catalog_Model_Product_Api();
			$productId = $prod_api->create($prod_type, $default_attribute_set_id, $itemcode, $omx2mage_product_data_map);
			$ret_msg = $sku . " created";
			Mage::Log($ret_msg, Zend_Log::INFO, "bizsyncxl.log");
		}
		
		return "OK";
	}
	
	
}