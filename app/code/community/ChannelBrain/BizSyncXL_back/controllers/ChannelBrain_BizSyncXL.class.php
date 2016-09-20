<?php
/**
 * ChannelBrain_BizSyncXL_Magento class
 *
 * @package ChannelBrain_BizSyncXL
 * @author Gary MacDougall
 **/

define ('MODULE_BASE_VERSION', '1.0.5');


class ChannelBrain_BizSyncXL
{
	var $ActionValues;
	
	/**
	 * Action_SyncProduct function.
	 * 
	 * @access public
	 * @return void
	 */
	function ChannelBrain_BizSyncXL ($ActionValues)
	{
		$this->ActionValues = $ActionValues;
	}
	
	/**
	 * Action_SyncProduct function.
	 * 
	 * @access public
	 * @return void
	 */
	public function Action_GetProductID()
	{
		writeStartTag("GetProductID");
		try
		{
			$sku = $this->ActionValues['sku'];
			$store_id = $this->ActionValues['store_id'];

			// go into admin mode
			Mage::app()->getStore()->setId(Mage_Core_Model_App::ADMIN_STORE_ID);
			$prod = Mage::getModel('catalog/product');
			$productId = $prod->getIdBySku($sku);
			if ($productId > 0)
			{
				writeElement("Success", 1);
				writeElement("Message", $sku . " exists.");
				writeElement("ResultID", $productId);
				writeElement("ErrorData", "");

			} else {
				writeElement("Success", 0);
				writeElement("Message", $sku . " not found.");
				writeElement("ResultID", 0);
				writeElement("ErrorData", "");
			}

		} catch (Exception $e) {
			writeElement("Success", 0);
			writeElement("Message", "Exception caught on line " . __LINE__ . " in " . __FUNCTION__);
			writeElement("ResultID", 0);
			writeElement("ErrorData", $e->GetMessage());
		}

		writeCloseTag("GetProductID");


	}

	/**
	 * Action_SyncProduct function.
	 * 
	 * @access public
	 * @return void
	 */
	public function Action_SyncProduct()
	{	

		$data="";

		try
		{
			$sku = $this->ActionValues['sku'];
			
			if ($this->ActionValues['compression'] == "1")
			{
				$data = gzuncompress($data);
			}
			$data = base64_decode ($this->ActionValues['data']);			

		} catch (Exception $e) {
			writeElement("Success", 0);
			writeElement("Message", "Exception caught on line " . __LINE__ . " in " . __FUNCTION__);
			writeElement("ResultID", 0);
			writeElement("ErrorData", $e->GetMessage());
		}

		try
		{
			writeStartTag("SyncProductResponse");
			$resource = Mage::getSingleton('core/resource');
			$read = $resource->getConnection('core_read');

			//$table = $resource->getTableName('bizsync_queue');
			$table = $resource->getTableName('catalog_product_entity');

			$select = $read->select()
			   ->from($table)
			  ->where('sku = ?',$sku)
			   ->order('sku DESC');

			$row = $read->fetchRow($select);


			if (count($row) && $row != false)
			{
				// update
				$product_id = intval($row['entity_id']);
				//
				// default_attribute_set_id is 4 because 'Default' is set to 4 in Magento.
				//
				$default_attribute_set_id = 4;
				$result = $this->syncExistingProduct($sku, $data, $default_attribute_set_id);
				if ($result != true)
				{
					writeElement("Success", 0);		
					writeElement("ResultID", $product_id);
					writeElement("Message", "Action_SyncProduct updating product " . $sku . " failed.");
					writeElement("ErrorData", $result);					
				} else {
					writeElement("Success", 1);		
					writeElement("Message",  $sku . " updated.");
					writeElement("ResultID",  $product_id);
					writeElement("ErrorData", "");					
				}
			} else {
				//insert
			 	$default_attribute_set_id = 4;
			        $product_id = $this->syncNewProduct($sku, $data, $default_attribute_set_id);
			        if ($product_id > 0)
			        {
					writeElement("Success", 1);		
					writeElement("ResultID", $product_id);
					writeElement("Message", $sku . " created.");
					writeElement("ErrorData", "");
			        } else {
					writeElement("Success", 0);
					writeElement("ResultID", 0);
					writeElement("Message", $sku . " insert failed.");
					writeElement("ErrorData", $product_id);
			        }
			}
		} catch (Exception $e) {
				writeElement("Success", 0);
				writeElement("Message", "Exception caught on line " . __LINE__ . " in " . __FUNCTION__);
				writeElement("ResultID", 0);
				writeElement("ErrorData", $e->GetMessage());

		}
		writeCloseTag("SyncProductResponse");		
	}

	/**
	 * Action_GetStores 
	 *
	 * @access public
	 * @return array
	 *
	 */
	 public function Action_GetStores ()
	 {
	    writeStartTag("Stores");
	    //writeElement("Start", $start);

	    $stores = Mage::getModel('core/store_group')
			->getResourceCollection()
			->setLoadDefault(true)
			->load();

		$stores = $stores->toArray();
		$stores = $stores['items'];

		$storeIds = array();
/*
    [group_id] => 2
    [website_id] => 2
    [name] =>  Store Name
    [root_category_id] => 2
    [default_store_id] => 2
*/

		foreach ($stores as $store) {
			if ($store['default_store_id'] != '0') {
				writeStartTag("Store");
				writeElement("store_id", $store['default_store_id']);
				writeElement("website_id", $store['website_id']);
				writeElement("store_name", $store['name']);
				writeCloseTag("Store");			
			} 
		}

	    writeCloseTag("Stores");				    

	 }

	/**
	 * Action_GetStore function.
	 * 
	 * @access public
	 * @return void
	 */
	public function Action_GetStore()
	{
	    // get state name
	    $region_model = Mage::getModel('directory/region');
	    if (is_object($region_model))
	    {
		$state = $region_model->load(Mage::getStoreConfig('shipping/origin/region_id'))->getDefaultName();
	    }

	    $name = Mage::getStoreConfig('system/store/name');
	    $owner = Mage::getStoreConfig('trans_email/ident_general/name');
	    $email = Mage::getStoreConfig('trans_email/ident_general/email');
	    $country = Mage::getStoreConfig('shipping/origin/country_id');
	    $website = Mage::getURL();

	    writeStartTag("Store");
	    writeElement("Name", $name);
	    writeElement("Owner", $owner);
	    writeElement("Email", $email);
	    writeElement("State", $state);
	    writeElement("Country", $country);
	    writeElement("Website", $website);
	    writeCloseTag("Store");
	}


	/**
	 * Action_SyncQuantities function.
	 * 
	 * @access public
	 * @return void
	 */
	public function Action_SyncQuantities()
	{	  
		writeStartTag("SyncQuantitiesResponse");

		try
		{
			$qty = $this->ActionValues['qty'];
			$sku = $this->ActionValues['sku'];
			$store_id = $this->ActionValues['store_id'];

			// go into admin mode
			Mage::app()->getStore()->setId(Mage_Core_Model_App::ADMIN_STORE_ID);
			$prod = Mage::getModel('catalog/product');
			$productId = $prod->getIdBySku($sku);
			if (intval($productId) > 0)
			{
				$in_stock = ($qty > 0) ? 1 : 0;
				if ($productId > 0)
				{
					$product_stock = new Mage_CatalogInventory_Model_Stock_Item_Api();
					$product_stock->update($productId, array('qty' => $qty, 'is_in_stock' => $in_stock));
					writeElement("Success", 1);
					writeElement("Message", $sku . " updated to a quantity of " . $qty . ".");
					writeElement("ResultID", $productId);
					writeElement("ErrorData", "");

				} else {
					writeElement("Success", 0);
					writeElement("Message", "Product not found or invalid sku.");
					writeElement("ResultID", 0);
					writeElement("ErrorData", "");
				}

			} else {
				// no product for quantity
					writeElement("Success", 1);
					writeElement("Message", $sku . " Quantity NOT updated it didn't exist.");
					writeElement("ResultID", $productId);
					writeElement("ErrorData", "");
			}

		} catch (Exception $e) {
			writeElement("Success", 0);
			writeElement("Message", "Exception: '" . $e->GetMessage() . "' caught on line " . __LINE__ . " in " . __FUNCTION__ . " for '" . $sku . "'");
			writeElement("ResultID", 0);
			writeElement("ErrorData", $e->GetMessage());
		}

		writeCloseTag("SyncQuantitiesResponse");
	}

	/**
	 * Action_SyncQuantities function.
	 * 
	 * @access public
	 * @return void
	 */
	public function Action_SyncPrices()
	{	  
		writeStartTag("SyncPricesResponse");

		try
		{
			$qty = $this->ActionValues['qty'];
			$sku = $this->ActionValues['sku'];
			$discount = $this->ActionValues['discount'];
			$price = $this->ActionValues['price'];

			$ctype = $this->ActionValues['ctype'];
			$ctype2 = $this->ActionValues['ctype2'];
			$ctype3 = $this->ActionValues['ctype3'];
			$from_date = $this->ActionValues['from_date'];
			$to_date = $this->ActionValues['to_date'];
			$pricelevel = $this->ActionValues['pricelevel'];
			$coupon = $this->ActionValues['coupon'];			

			$store_id = $this->ActionValues['store_id'];

			// gym: 08/16/11 - make sure we do percentage off discounts.
			// convert to a regular price.
			if($discount > 0)
			{
				if( $price > 0 )
				{
					$price = $price * (1 - ($discount / 100));
				}
			}


			// go into admin mode
			Mage::app()->getStore()->setId(Mage_Core_Model_App::ADMIN_STORE_ID);
			$prod = Mage::getModel('catalog/product');
			$productId = $prod->getIdBySku($sku);

			// let's update the base item price first.
			if ($qty == '1')
			{
				try
				{
					if( intval($productId) > 0 )
					{
						$prod_api =  new Mage_Catalog_Model_Product_Api();
						// this looks like its going to cause a world of hurt,
						// do you need to LOAD the product, then change the price, then save ?
						$product['price'] = $price;
						$prod_api->update($productId, $product, Mage_Core_Model_App::ADMIN_STORE_ID);
					}
					else
					{
						writeElement("Success", 0);
						writeElement("Message", "Error no product id found for '" . $sku . "'");
						writeElement("ResultID", 0);
						writeElement("ErrorData", "update failed.");
						writeCloseTag("SyncPricesResponse");
						return;

					}
				} catch (Exception $e) {
					writeElement("Success", 0);
					writeElement("Message", "Exception caught on line " . __LINE__ . " in " . __FUNCTION__ . " with error '" . $e->GetMessage() . "' for '" . $sku . "'");
					writeElement("ResultID", 0);
					writeElement("ErrorData", $e->GetMessage());
					writeCloseTag("SyncPricesResponse");
					return;
				}
			}

			if (intval($productId) > 0)
			{

				$mcmpata = new Mage_Catalog_Model_Product_Attribute_Tierprice_Api();
				//$tierprices = $mcmpata->info($productId);

				$tierprices2 = $mcmpata->info($productId);
				$tierprices = array();

				foreach ($tierprices2 as $key=>$tierprice)
				{
					if ($tierprice['qty'] != '1')
					{
						$tierprices[$key] = $tierprices2[$key];
					}
				}
				$bFoundTierPrice = false;				

				foreach ($tierprices as $key=>$tierprice)
				{
					if ($tierprice['qty'] == $qty)
					{
						$tierprices[$key]['price'] = $price;
						$bFoundTierPrice = true;
					}
				}
				// if we didn't find it and its not qty of 1, then add it
				if (!$bFoundTierPrice && $qty > 1)
				{
					$tierprices[] = array('website'           => 'all',
							      'customer_group_id' => 'all',
							      'qty'               => $qty,
							      'price'             => $price);
				}

				try
				{
					$mcmpata->update($productId, $tierprices);
					writeElement("Success", 1);
					writeElement("Message", $sku . " Price updated.");
					writeElement("ResultID", $productId);
					writeElement("ErrorData", "");

				} catch(Exception $e) {
					writeElement("Success", 0);
					writeElement("Message", "Exception caught on line " . __LINE__ . " in " . __FUNCTION__ . " with error '" . $e->GetMessage() . "' for '" . $sku . "'");
					writeElement("ResultID", 0);
					writeElement("ErrorData", $e->GetMessage());
				}

			} else {
				writeElement("Success", 1);
				writeElement("Message", "Product does not exist for price line. Sync will continue.");
				writeElement("ResultID", 0);
				writeElement("ErrorData", "Product does not exist.");
			}

		} catch (Exception $e) {
			writeElement("Success", 0);
			writeElement("Message", "Exception caught on line " . __LINE__ . " in " . __FUNCTION__ . " with error '" . $e->GetMessage() . "' for '" . $sku . "'");			
			writeElement("ResultID", 0);
			writeElement("ErrorData", $e->GetMessage());
		}
		writeCloseTag("SyncPricesResponse");
	}


	/**
	 * Action_GetCount function.
	 * 
	 * @access public
	 * @return void
	 */
	public function Action_GetCount()
	{	  
	    $start = 0;

	    if (isset($_REQUEST['start']))
	    {
		$start = $_REQUEST['start'];
	    }

	    // only get orders through 2 seconds ago
	    $end = date("Y-m-d H:i:s", time() - 2);

	    // Convert to local SQL time
	    $start = toSqlDate($start);

	    // Write the params for easier diagnostics
	    writeStartTag("Parameters");
	    writeElement("Start", $start);
	    writeCloseTag("Parameters");

	    $orders = Mage::getModel('sales/order')->getCollection();
	    $orders->addAttributeToSelect("updated_at")->getSelect()->where("(updated_at > '$start' AND updated_at <= '$end')");
	    $count = $orders->count();

	    writeElement("OrderCount", $count);
	}

	/**
	 * Action_GetOrders function.
	 * 
	 * @access public
	 * @return void
	 */
	public function Action_GetOrderPaymentData()
	{
		$increment_id = $_REQUEST['increment_id'];

		if ($increment_id == "") {
			$increment_id = $this->ActionValues['increment_id'];		
		}

		$order = Mage::getModel('sales/order')->loadByIncrementId($increment_id);
		$payment = $order->getPayment();

		if (!is_object ($order))
		{
			writeElement("Success", 0);
			writeElement("Message", "Error on line " . __LINE__ . " in " . __FUNCTION__);
			writeElement("ResultID", 0);
			writeElement("ErrorData", "No order id found");
			return;
		}
		$payment = $order->getPayment();

		$cc_num = $payment->getCcLast4();
		$cc_cvv = "";
		$cc_year = sprintf('%02u%s', $payment->getCcExpMonth(), substr($payment->getCcExpYear(), 2));

		if (!empty($cc_num)){
		$cc_num = '************'.$payment->getCcLast4();
		}
		else {
		$cc_year = '';
		};


		writeStartTag("Payment");
		$method = Mage::helper('payment')->getMethodInstance($payment->getMethod())->getTitle();

		$data = $payment->getData();
		if ($data['method'] == "ccsave")
		{
			$cc_num = $payment->decrypt ($payment->getCcNumberEnc());
			// try to get the cvv
			$sql = "SELECT cc_cid_enc FROM sales_flat_quote_payment WHERE quote_id =" . $order["quote_id"];
			$cc_cid_enc = Mage::getSingleton('core/resource')->getConnection('core_read')->fetchOne($sql); 
			$cc_cvv = $payment->decrypt ($cc_cid_enc);
		} 
		writeElement("Method", $method);

		writeElement ("TransactionId", $data['cc_trans_id']);
		writeElement ("ApprovalCode", $data['cc_approval']);
		writeElement ("AVSCode", $data['cc_avs_status']);

		writeStartTag("CreditCard");
		writeElement("Type", $data['cc_type']);
		writeElement("Owner", $payment->getCcOwner());
		writeElement("Number", $cc_num);
		writeElement("CVV", $cc_cvv);
		writeElement("Expires", $cc_year);
		writeCloseTag("CreditCard");

		writeCloseTag("Payment");

		writeStartTag("ServiceMessage");

		writeElement("Success", 1);
		writeElement("Message", "Payment data valid.");
		writeElement("ResultID", 1);
		writeElement("ErrorData", "");

		writeCloseTag("ServiceMessage");

		return;
	}


	/**
	 * Action_GetOrders function.
	 * 
	 * @access public
	 * @return void
	 */
	public function Action_GetOrders()
	{
	    $start = 0;
	    $maxcount = 50;

	    if (isset($this->ActionValues['start']))
	    {
		$start = $this->ActionValues['start'];
	    }

	    if (isset($this->ActionValues['maxcount']))
	    {
		$maxcount = $this->ActionValues['maxcount'];
	    }

	    // Only get orders through 2 seconds ago.
	    $end = date("Y-m-d H:i:s", time() - 2);

	    // Convert to local SQL time
	    $start = toSqlDate($start);

	    // Write the params for easier diagnostics
	    writeStartTag("Parameters");
	    writeElement("Start", $start);
	    writeElement("End", $end);
	    writeElement("MaxCount", $maxcount);
	    writeCloseTag("Parameters");				    

	    $orders = Mage::getModel('sales/order')->getCollection();
	    $orders->addAttributeToSelect("*")
		->getSelect()
		->where("(status = 'Pending')")
		->order('updated_at', 'asc');
            $orders->setCurPage(1)
                ->setPageSize($maxcount)
                ->loadData();
	    //		->where("(updated_at > '$start' AND updated_at <= '$end')")
	    writeElement("Total", $orders->count());

	    writeStartTag("Orders");

	    $lastModified = null;
	    $processedIds = "";

	    foreach ($orders as $order)
	    {
		// keep track of the ids we've downloaded
		$lastModified = $order->getUpdatedAt();

		if ($processedIds != "")
		{
		    $processedIds .= ", ";
		}
		$processedIds .= $order->getEntityId();

		WriteOrder($order);
	    }

	    // if we processed some orders we may have to get some more
	    if ($processedIds != "")
	    {
		$orders = Mage::getModel('sales/order')->getCollection();
		$orders->addAttributeToSelect("*")->getSelect()->where("updated_at = '$lastModified' AND entity_id not in ($processedIds) ");

		foreach ($orders as $order)
		{
		    WriteOrder($order);
		}
	    }

	    writeCloseTag("Orders");
	}



	/**
	 * Action_GetConfiguration function.
	 * 
	 * @access public
	 * @return void
	 */
	public function Action_GetConfiguration()
	{
		$resource = Mage::getSingleton('core/resource');
		$read= $resource->getConnection('core_read');

		$omxTable = $resource->getTableName('omx');

		$select = $read->select()
		   ->from($omxTable)
		   ->where('status',1)
		   ->order('created_time DESC');

		$row = $read->fetchRow($select);
		foreach ($row as $key=>$value)
		{
			writeElement($key, $value);
		}

	}

	/**
	 * Action_TestConnection function.
	 * 
	 * @access public
	 * @return void
	 */
	public function Action_TestConnection()
	{
	    writeStartTag("TestConnection");
		if ($this->checkAdminLogin())
		{
			writeStartTag("StatusCode");
			writeElement("Code", 0);
			writeElement("Name", "Login succeeded.");
			writeCloseTag("StatusCode");
		} else {
			writeStartTag("StatusCode");
			writeElement("Code", 1);
			writeElement("Name", "Login failed.");
			writeCloseTag("StatusCode");
		}
	    writeCloseTag("TestConnection");
	}

	/**
	 * Action_GetStatusCodes function.
	 * 
	 * @access public
	 * @return void
	 */
	public function Action_GetStatusCodes()
	{
	    writeStartTag("Configuration");

	    $statuses_node = Mage::getConfig()->getNode('global/sales/order/statuses');

	    foreach ($statuses_node->children() as $status)
	    {
		writeStartTag("StatusCode");
		writeElement("Code", $status->getName());
		writeElement("Name", $status->label);
		writeCloseTag("StatusCode");
	    }

	    writeCloseTag("Configuration");
	}


	/**
	 * Action_GetShippingMethods function.
	 * 
	 * @access public
	 * @return void
	 */
	public function Action_GetShippingMethods()
	{
		$shipping = new Mage_Shipping_Model_Config();
		writeStartTag("ShippingMethods");
		foreach ($shipping->getActiveCarriers() as $name=>$carrier)
		{
			$allowed_methods = $carrier->getAllowedMethods();
			if( count($allowed_methods) == 1 )
			{
				if( $carrier['title'] != "")
				{
					writeStartTag("Method");
					writeElement("Name", $carrier['title'] . " - " . $carrier['name']);
					writeElement ("Carrier", $name);
					writeCloseTag("Method");
				}
				else
				{
					writeStartTag("Method");
					writeElement ("Carrier", $name);
					writeElement("Name", $name . "_" . $name);
					writeCloseTag("Method");
				}
			}
			else
			{
				foreach ($allowed_methods as $method=>$value)
				{

					if (trim($value) != "") 
					{
						writeStartTag("Method");
						writeElement ("Carrier", $name);						
						writeElement ("Name", $name . "_" . $method);
						writeCloseTag("Method");
					}
					else
					{
						// 2011 June 15 - UPS XML labels are not showing up, so ask the UPS class what they are.
						// should we verify that carrier is type Mage_Usa_Model_Shipping_Carrier_Ups ?
						$arr = $carrier->getCode('originShipment');
						$val = $arr['United States Domestic Shipments'][$method];
						writeStartTag("Method");
						//writeElement ("Name", $val);
						writeElement("Name", $name . "_" . $method );
						writeElement ("Carrier", $name);
						writeCloseTag("Method");

					}
				}
			}		
		}
		writeCloseTag("ShippingMethods");
	}


	/**
	 * Action_GetPaymentMethods function.
	 * 
	 * @access public
	 * @return void
	 */
	public function Action_GetPaymentMethods()
	{
		$payment = new Mage_Payment_Helper_Data();

		writeStartTag("PaymentMethods");

		foreach ($payment->getStoreMethods() as $method=>$value)
		{
		     if (is_object($value)) {
			writeStartTag("Method");
			writeElement ("Name", $value->getTitle());
			writeElement ("Code", $value->getCode());
			writeCloseTag("Method");
			}
		}
		writeCloseTag("PaymentMethods");
	}

	/**
	 * Action_GetCreditCartTypes function.
	 * 
	 * @access public
	 * @return void
	 */
	public function Action_GetCreditCardTypes()
	{
		$cards = new Mage_Payment_Model_Config();

		writeStartTag("PaymentMethods");
		foreach ($cards->getCcTypes() as $key=>$value)
		{
		     if (trim($value) != "") {
			writeStartTag("Method");
			writeElement ("Name", $key);
			writeCloseTag("Method");
			}
		}
		writeCloseTag("PaymentMethods");
	}


	/**
	 * Action_UpdateOrder function.
	 * 
	 * Update the status of an order
	 * command: hold,complete,cancel
	 * orderid: order number (alt_order)
	 * tracking: the tracking number of the order
	 *
	 * @access public
	 * @return void
	 */
	public function Action_UpdateOrder()
	{
	    // gather paramtetes
	    if ((!isset($_REQUEST['order']) && !isset($_REQUEST['orderid'])) || 
	    	!isset($_REQUEST['command']) || !isset($_REQUEST['comments']))
	    {
		RestResultError(40, "Not all parameters supplied.");
		return;
	    }

	    if (isset($_REQUEST['order']))
	    {
	    	$orderNumber = (int) $_REQUEST['order'];
	    	$order = Mage::getModel('sales/order')->loadByIncrementId($orderNumber);
	    }
	    else
	    {
	    	// newer version of BizSync, pull the entity id
		$orderID = (int)$_REQUEST['orderid'];
		$order = Mage::getModel('sales/order')->load($orderID);
	    }

	    $command = (string) $_REQUEST['command'];
	    $comments = $_REQUEST['comments'];
	    $tracking = $_REQUEST['tracking'];
	    $carrierData = $_REQUEST['carrier'];

	    ExecuteOrderCommand($order, $command, $comments, $carrierData, $tracking);
	}

	/**
	 * Action_DeleteProduct function.
	 * 
	 * Delete's a product and its associated products (if it's a configurable product).
	 * 
	 * @access public
	 * @return void
	 */
	public function Action_DeleteProduct()
	{
		writeStartTag("DeleteProduct");
		try
		{
			$sku = GetSKU();
			$store_id = $this->ActionValues['store_id'];

			// go into admin mode
			Mage::app()->getStore()->setId(Mage_Core_Model_App::ADMIN_STORE_ID);
			$product = Mage::getModel('catalog/product');
			$productId = $product->getIdBySku($sku);
			$product->load ($productId);
			$productIds = array ($productId);
			if (intval($productId) > 0 && is_object($product))
			{
				$configurable = false;
				if ($product->getTypeId() == "configurable")
				{
					$associatedProducts = Mage::getSingleton('catalog/product_type')->factory($product)->getUsedProducts($product); 

					foreach($associatedProducts as $associatedProduct) 
					{ 
						$productIds[] = $associatedProduct->getId();
					} 
				}
				$prod_api =  new Mage_Catalog_Model_Product_Api();

				foreach ($productIds as $prodId)
				{
					if ($prod_api->delete($prodId))
					{
						writeElement("Success", 1);
						writeElement("Message", $sku . " deleted.");
						writeElement("ResultID", $prodId);
						writeElement("ErrorData", "");
					} else {
						writeElement("Success", 0);
						writeElement("Message", $sku . " could not be deleted.");
						writeElement("ResultID", 0);
						writeElement("ErrorData", "");
					}
				}


			} else {
				writeElement("Success", 0);
				writeElement("Message", $sku . " not found or invalid sku.");
				writeElement("ResultID", 0);
				writeElement("ErrorData", "");
			} 

		} catch (Exception $e) {
			writeElement("Success", 0);
			writeElement("Message", "Exception caught on line " . __LINE__ . " in " . __FUNCTION__);
			writeElement("ResultID", 0);
			writeElement("ErrorData", $e->GetMessage());
		}

		writeCloseTag("DeleteProduct");
	}


	/**
	 * Action_DeleteProduct function.
	 * 
	 * Delete's a product and its associated products (if it's a configurable product).
	 * 
	 * @access public
	 * @return void
	 */
	public function Action_RemoveCreditCard()
	{
		$increment_id = $this->ActionValues['increment_id'];		
		$order = Mage::getModel('sales/order')->loadByIncrementId($increment_id);
		$payment = $order->getPayment();
		try
		{
			if (!is_object ($order) || !is_object($payment))
			{
				writeElement("Success", 0);
				writeElement("Message", "Error on line " . __LINE__ . " in " . __FUNCTION__);
				writeElement("ResultID", 0);
				writeElement("ErrorData", "No order id found");
				return;
			}
/*
			SELECT `cc_number_enc` FROM `sales_flat_order_payment` WHERE entity_id = 17

			UPDATE  `DATABASE_NAME`.`sales_flat_order_payment` SET  `cc_number_enc` =  '' WHERE  `entity_id`=  '17' LIMIT 1 ;
*/
			writeElement("Success", 1);
			writeElement("Message", $increment_id . " card was removed.");
			writeElement("ResultID", $increment_id);
			writeElement("ErrorData", "");


		} catch (Exception $e) {
			writeElement("Success", 0);
			writeElement("Message", "Exception caught on line " . __LINE__ . " in " . __FUNCTION__);
			writeElement("ResultID", 0);
			writeElement("ErrorData", $e->GetMessage());
		}
		writeCloseTag("RemoveCreditCard");
	}


	/**
	 * Action_DisableProduct function.
	 * 
	 * @access public
	 * @return void
	 */
	public function Action_DisableProduct()
	{
		// this disables the product (turns the Enable flag off).
	}

	/**
	 * Action_SyncImages function.
	 * 
	 * @access public
	 * @return void
	 */
	public function Action_SyncImage()
	{
	    writeStartTag("SyncImageResponse");

		$stocknumber = $this->ActionValues['sku'];
		$image_data = $this->ActionValues['image_data'];
		$image_name = $this->ActionValues['image_name'];
		$image_type = $this->ActionValues['image_type'];

		Mage::app()->getStore()->setId(Mage_Core_Model_App::ADMIN_STORE_ID);	
		$prod = Mage::getModel('catalog/product');
		$productId = $prod->getIdBySku($stocknumber);


		// make sure prod is initialized
		if( !is_numeric ($productId) )
		{
			// failed.  return with error.
			writeElement("Success", 0);
			writeElement("Message", "catalog/product failed to load.");
			writeElement("ResultID", 0);
			writeElement("ErrorData", "");
			writeCloseTag("SyncImageResponse");
			// don't continue.
			die();	
		}

		if( is_numeric( $productId ) )
		{
			$prod->load($productId);
			$prod->setTypeId($prod_type);

			// always's use the magento media/import folder.
			$import_path = "media/import";

			if( strtolower($image_type) == "image" ||
			    strtolower($image_type) == "thumbnail" ||
			    strtolower($image_type) == "small_image")
			{
				$base_path = $_SERVER["DOCUMENT_ROOT"] . Mage::app()->getRequest()->getBasePath();
				$fullImagePath = Mage::getBaseDir('media') . "/import/" . $image_name;
				if ($image_data != "")
				{
					file_put_contents($fullImagePath, base64_decode (trim($image_data)));
					if(file_exists($fullImagePath) && is_file($fullImagePath))
					{
						$result = AddProductImage( $productId, $fullImagePath, $image_type );
						if ($result != "") 
						{
							writeElement("Success", 1);
							writeElement("Message", $result);
							writeElement("ResultID", $productId);
							writeElement("ErrorData", "");

						} else {
							writeElement("Success", 1);
							writeElement("Message", $image_name . " added.");
							writeElement("ResultID", $productId);
							writeElement("ErrorData", "");
						}

					} else {
						writeElement("Success", 0);
						writeElement("Message", $image_name . " failed.");
						writeElement("ResultID", $productId);
						writeElement("ErrorData", "");
					}
				} else {
					// failed.  
					writeElement("Success", 0);
					writeElement("Message", "Image data empty.");
					writeElement("ResultID", 0);
					writeElement("ErrorData", "");
				}
			} else {
				// failed.  
				writeElement("Success", 0);
				writeElement("Message", "Image type '" . $image_type . "' invalid.");
				writeElement("ResultID", 0);
				writeElement("ErrorData", "");
			}
		}
	    writeCloseTag("SyncImageResponse");

	}

	/**
	 * Action_GetProductFields function
	 *
	 * @return void
	 * @author Gary MacDougall
	 **/
	public function Action_GetProductFields()
	{
		$shipping = new Mage_Shipping_Model_Config(); 
		writeStartTag("ProductFields");
		foreach ($shipping->getActiveCarriers() as $carrier)
		{
			foreach ($carrier->getAllowedMethods() as $method=>$value)
			{
				writeStartTag("Method");
				writeElement ("Name", $value);
				writeCloseTag("Method");
			}		
		}
		writeCloseTag("ProductFields");
	}

	/**
	 * checkAdminLogin function.
	 * 
	 * @access public
	 * @return void
	 */
	public function checkAdminLogin()
	{
	    $loginOK = false;
	    $XLUsername = "";
	    $XLPassword = "";
	    // posted vs. request.
	    try {
		    if (isset($this->ActionValues['XLUsername']) && isset($this->ActionValues['XLPassword']))  
		    {
			$XLUsername = $this->ActionValues['XLUsername'];
			$XLPassword = $this->ActionValues['XLPassword'];

		    } 
		    if ($XLUsername != "" && $XLPassword != "")
		    {
			$user = Mage::getSingleton('admin/session')->login($XLUsername, $XLPassword);
			if ($user && $user->getId())
			{
			    $loginOK = true;	    
			}
		    }
		    if (!$loginOK)
		    {
			RestResultError(50, "The username or password is incorrect.");
		    }

		
		} catch (Exception $e) {
			RestResultError(50, "function " . __FUNCTION__ . " threw the exception " . $e->GetMessage());
		}
		return $loginOK;
	}

	/**
	 * syncNewProduct function.
	 * 
	 * @access private
	 * @param mixed $itemcode
	 * @param mixed $data
	 * @param mixed $default_attribute_set_id
	 * @return void
	 */
	private function syncNewProduct($itemcode, $data, $default_attribute_set_id)
	{
		global $BizSyncStoreID;
		global $magento;
		global $session;

		$prod=null;

		try
		{
			$xml = simplexml_load_string ($data);
			if (get_class($xml) != "SimpleXMLElement")
			{
				return "Error: (syncNewProduct) XML invalid and could not parse.";
			}

			$item_attributes = $xml->WebStore->ItemData->Item->attributes();
			$stocknumber = $item_attributes['itemCode'];

			if ($stocknumber == '')
			{
				return "SKU is empty.";
			}

			if ($stocknumber != $itemcode)
			{
				return "Item code '" . $itemcode ."' in XML did not match SKU that was passed.";
			}
			$taxable = ($item_attributes['isTaxable']) == "True" ? 1 : 0;
			$is_active = ($item_attributes['active']) == "True" ? 1 : 2;

			$discontinued = (trim($xml->WebStore->ItemData->Item->Discontinued[0])) == "True" ? 1 : 0;

			$wgt = escapeInventoryFileData($xml->WebStore->ItemData->Item->Weight[0]);

			$short_description = "";
			$description = "";
			$product_name = "";

			$sync_product_longdescription = trim($this->ActionValues['sync_product_longdescription']);
			$sync_product_shortdescription = trim($this->ActionValues['sync_product_shortdescription']);
			$sync_product_name = trim($this->ActionValues['sync_product_name']);

			if ($sync_product_shortdescription == "1") {
				$short_description  = trim($xml->WebStore->ItemData->Item->ProductName[0]);
			}

			if ($sync_product_shortdescription == "1") {
				$description = trim ($xml->WebStore->ItemData->Item->InfoText[0]);
			}

			if ($sync_product_name == "1") {
				$product_name = trim ($xml->WebStore->ItemData->Item->ProductName[0]);
			}

			// we do need a description when we create the intiial product, magento will not allow
			// the product to be created.
			if ($description == "")
			{
				// we need to make sure that we have SOMETHING, use the SKU.
				$description = GetLatinLongDescription();
			}

			if ($short_description == "")
			{
				$short_description = GetLatinShortDescription();
			}

			if ($product_name == "")
			{
				$product_name = $stocknumber . " " . GetLatinProductName();
			}

			$row_serialized = '';
			if (trim($xml->WebStore->RowData[0]) != '')
			{
				$row_data = base64_decode (trim($xml->WebStore->RowData[0]));
				$row_array = json_decode(json_encode((array) simplexml_load_string($row_data)),1);
				$row_serialized = serialize ($row_array);
			}


			// map channelbrain to Magento.
			$omx2mage_product_data_map = array(
				'name'              => escapeInventoryFileData($product_name),
				'weight'	    => $wgt ? $wgt : 1,
				'websites'          => array(intval($BizSyncStoreID)),
				'short_description' => escapeInventoryFileData($short_description),		
				'description'       => escapeInventoryFileData($description),
				'price'             =>  str_replace(",","",escapeInventoryFileData($xml->WebStore->ItemData->Item->PriceData->Price->Amount[0])),
				'tax_class_id'	=> '2',
				'meta_rowdata'	=> $row_serialized,
				'status'	=> $is_active
			);

			//$omx2mage_product_data_map = SetSyncFieldOptions ($omx2mag_product_data_map);

			$prod_type = "simple";

			// Are we a configurable product with subItems ?
			if (count($xml->WebStore->ItemData->Item->SubItem) > 0 )
			{
				$prod_type = "configurable";
			}

			// go into admin mode   ????????
			Mage::app()->getStore()->setId(Mage_Core_Model_App::ADMIN_STORE_ID);	
			$prod = Mage::getModel('catalog/product');
			$productId = $prod->getIdBySku($stocknumber);

			if ($prod_type == "simple")
			{

				if( $productId > 0 )
				{
					// if updating, we should be in syncExistingProduct(), not here
					return "Error in " . __FUNCTION__ . " on line " . __LINE__ . " : " .  " product id already exists.";
				}
				else
				{
					$prod_api =  new Mage_Catalog_Model_Product_Api();
					$productId = $prod_api->create($prod_type, $default_attribute_set_id, $itemcode, $omx2mage_product_data_map);
				}

				// set the inventory quantity
				$qty = intval($xml->WebStore->ItemData->Item->Available[0]);
				$in_stock = ($qty > 0) ? 1 : 0;

				$product_stock = new Mage_CatalogInventory_Model_Stock_Item_Api();
				$product_stock->update($productId, array('qty' => $qty, 'is_in_stock' => $in_stock));
			}

			// init configurable product and handle sub items
			if( $prod_type == "configurable" )
			{
				$attribute_ids = array();
				$subitem_ids   = array();
				$subitem_skus   = array();

				// do subitems...
				if( count($xml->WebStore->ItemData->Item->SubItem) > 0 )
				{
					$result = $this->DoSubItems($xml, $default_attribute_set_id, $omx2mage_product_data_map, $itemcode, $attribute_ids, $subitem_ids, $subitem_skus);
					if ($result != "")
					{
						return $result;
					} 
				}

				if( $productId > 0 )
				{
					return "Error in " . __FUNCTION__ . " on line " . __LINE__ . " : " .  " product id already exists.";
				}
				else
				{
					$prod_api =  Mage::getSingleton("catalog/product_api",array("name"=>"api"));
					$omx2mage_product_data_map["associated_skus"] = $subitem_skus;
					// lets ask one of the simple products created in DoSubItems what the config attrs are...
					$prod2 = Mage::getModel('catalog/product');
					$prod2->load($prod2->getIdBySku($subitem_skus[0]));
					foreach($attribute_ids as $the_id)
					{

						$attr = $prod2->getResource()->getAttribute($the_id); //Mage_Catalog_Model_Resource_Eav_Attribute
						$omx2mage_product_data_map[$attr->getName()] = "";
					}
					$productId = $prod_api->create($prod_type, $default_attribute_set_id, $itemcode, $omx2mage_product_data_map);					 
				}

				try
				{
					// tell it to use config for stock inventory - use_config_manage_stock
					$prod->load($productId);
					$prod->setTypeId($prod_type);
					$prod->getTypeInstance()->setUsedProductAttributeIds ($attribute_ids) ;
					$ConfigurableAttributesAsArray = $prod->getTypeInstance()->getConfigurableAttributesAsArray($prod);
					$ConfigurableAttributesAsArray[0]["store_label"] = $ConfigurableAttributesAsArray[0]["frontend_label"];
					$ConfigurableAttributesAsArray[0]["label"] = $ConfigurableAttributesAsArray[0]["frontend_label"];
					$prod->setConfigurableAttributesData($ConfigurableAttributesAsArray);
					$prod->setCanSaveConfigurableAttributes(true);
					$prod->setCanSaveCustomOptions(true);
					$prod->setConfigurableProductsData($subitem_ids) ;
					// save.
					$prod->setIsMassupdate(true);
					$prod->setExcludeUrlRewrite(true);
					$prod->getTypeInstance()->save();


				} catch(Exception $e) {
				  return "Exception in " . __FUNCTION__ . " on line " . __LINE__ . " : " .  $e->GetMessage();
				}
			} // end if

			// now do the image data.
			if( is_numeric( $productId ) )
			{

				if( count($xml->WebStore->ItemData->Item->ImageData->Image) > 0 )
				{
					$numAD = count($xml->WebStore->ItemData->Item->ImageData->Image);

					// remove any existing images this product may have had.
					$mediaApi = Mage::getModel("catalog/product_attribute_media_api");

					$items = $mediaApi->items($productId);
					if (count ($items))
					{
						foreach($items as $item)
						{
							$result = $mediaApi->remove($productId, $item['file']);
						}
					}

					for($k=0; $k<$numAD; $k++)
					{
						$attr = $xml->WebStore->ItemData->Item->ImageData->Image[$k]->attributes();				
						if( strtolower($attr["type"]) == "image" ||
						    strtolower($attr["type"]) == "thumbnail" ||
						    strtolower($attr["type"]) == "small_image")
						{
							$filename = $attr['filename'];
							$tag = $attr['tag'];
							$image_type = $attr["type"]; // image, small_image, or thumbnail
							$fullImagePath = Mage::getBaseDir('media') . "/import/" . $filename;
							if ($attr['image_data'] != "")
							{
								// always overwrite the file.
								file_put_contents($fullImagePath, base64_decode (trim($attr['image_data'])));
								if(file_exists($fullImagePath) && is_file($fullImagePath))
								{

									$result = $this->AddProductImage( $productId, $fullImagePath, $image_type );
								} else {
									return "Image '" . $filename . "' did not exist.";
								}
							} 
						}
					}
				}
			}

			// add tier pricing
			if( is_numeric( $productId ) && count($xml->WebStore->ItemData->Item->PriceData->Price) > 0 )
			{
				$mcmpata = new Mage_Catalog_Model_Product_Attribute_Tierprice_Api();
				$tierprices = array();
				$numPr = count($xml->WebStore->ItemData->Item->PriceData->Price);
				for($m=0; $m<$numPr; $m++)
				{
					$attr = $xml->WebStore->ItemData->Item->PriceData->Price[$m]->attributes();
					$quan = intval($attr["quantity"]);
					$bMultiplier = $attr["multiplier"];
					$amount = floatval($xml->WebStore->ItemData->Item->PriceData->Price[$m]->Amount[0]);
					$SH = floatval($xml->WebStore->ItemData->Item->PriceData->Price[$m]->AdditionalSH[0]);
					$tierprices[] = array('website'=>'all',
					'customer_group_id' => 'all',
					'qty'               => $quan,
					'price'             => $amount);
				}
				try
				{
					$mcmpata->update($productId, $tierprices);
				} catch(Exception $e) {
				  	return "Exception in " . __FUNCTION__ . " on line " . __LINE__ . " : " .  $e->GetMessage();
				}
			}

	    } catch (Exception $e) {
		return "Exception in " . __FUNCTION__ . " on line " . __LINE__ . " : " .  $e->GetMessage();
	    }

	    return $productId;
	}

	/**
	 * DoSubItems function.
	 * 
	 * @access private
	 * @param mixed &$xml
	 * @param mixed $default_attribute_set_id
	 * @param mixed $omx2mage_product_data_map
	 * @param mixed $parent_stocknumber
	 * @param mixed &$attribute_ids
	 * @param mixed &$subitem_ids
	 * @param mixed &$subitem_skus
	 * @return void
	 */
	private function DoSubItems(&$xml, $default_attribute_set_id, $omx2mage_product_data_map, $parent_stocknumber, &$attribute_ids, &$subitem_ids, &$subitem_skus)
	{

		$numSubItems = count($xml->WebStore->ItemData->Item->SubItem);
		if( 0 == $numSubItems )
		{
			return;
		}
		$base_name = $omx2mage_product_data_map["name"];

		// massage the DimensionData so we can use it later when looping thru subitems
		$variation_theme = "";
		$map_of_dd = $this->massageDimensionData($xml->WebStore->ItemData->Item->DimensionData, $variation_theme);
		$variation_dims = explode("-", strtolower($variation_theme) );

		// find the "dimension" attributes
		$prod_attr_api = new Mage_Catalog_Model_Product_Attribute_Api();
		$attrs = $prod_attr_api->items($default_attribute_set_id);
		$num_attra = count($attrs);
		$num_dims = count($variation_dims);

		for($y=0; $y<$num_dims; $y++)
		{
			$bfound = 0;
			$code = $variation_dims[$y];
			for($z=0; $z<$num_attra; $z++)
			{

				if($code == $attrs[$z]["code"] )
				{
					$attribute_ids[$code] = $attrs[$z]["attribute_id"];
					$bfound = 1;
				}
			}
			if( !$bfound )
			{
				$attribute_ids[$code] = $this->AddMageAttribute( $code , $default_attribute_set_id);
			}
		}


		$prod_api =  new Mage_Catalog_Model_Product_Api();
		for($i=0; $i<$numSubItems; $i++)
		{

			$sub_attributes = $xml->WebStore->ItemData->Item->SubItem[$i]->attributes();
			//$sub_stocknumber = str_replace("-", "_DASH_", $sub_attributes['itemCode']);
			$sub_stocknumber = strval($sub_attributes['itemCode']);
			$sub_sku = strval($sub_attributes['itemCode']);
			$subitem_skus[] = $sub_sku;

			$sizecolor_description = "";
			$the_size = "";
			$the_color = "";
			$sur = 0.00;
			// get the desc and surcharge from dimension data 
			$numDims = count($xml->WebStore->ItemData->Item->SubItem[$i]->ItemDimension);
			for($q=0; $q<$numDims; $q++)
			{
				$attr = $xml->WebStore->ItemData->Item->SubItem[$i]->ItemDimension[$q]->attributes();
				$name = $attr["name"];
				$key  = $name . "_" . $xml->WebStore->ItemData->Item->SubItem[$i]->ItemDimension[$q];

				if ($q > 0)
				{
					$sizecolor_description .= " ";
				}
				$sizecolor_description .=  $name . " " . $map_of_dd[$key]["Description"] ;
				$sur += $map_of_dd[$key]["Surcharge"];

				$lname = strtolower($name);
				if(array_key_exists($lname,$attribute_ids))
				{
					$the_dim_id = 0;
					$the_dim = strval($map_of_dd[$key]["Description"]);

					// we need to get the existings sizes and colors
					//$dim_options = $prod_attr_api->options($attribute_ids[$lname]);
					$attribute_model        = Mage::getModel('eav/entity_attribute');
					$attribute_options_model= Mage::getModel('eav/entity_attribute_source_table') ;
					$attribute              = $attribute_model->load($attribute_ids[$lname]);
					$attribute_table        = $attribute_options_model->setAttribute($attribute);
					$dim_options            = $attribute_options_model->getAllOptions(false);

					// now loop thru the dim_options and find the option_id for the_dim
					foreach($dim_options as $d_opt)
					{
						if($the_dim == $d_opt["label"])
						{
							$the_dim_id = $d_opt["value"];
							break;
						}
					}
					// if we did not find it, then create it
					if($the_dim_id == 0)
					{
						
						$attribute_model        = Mage::getModel('eav/entity_attribute');
						$attribute_options_model= Mage::getModel('eav/entity_attribute_source_table') ;
						$attribute              = $attribute_model->load($attribute_ids[$lname]);
						
						$new_value['option'] = array($the_dim,$the_dim);
						$result = array('value' => $new_value);
						$attribute->setData('option',$result);
						$attribute->save();
						$attribute_table        = $attribute_options_model->setAttribute($attribute);
						$dim_options            = $attribute_options_model->getAllOptions(false);

						// now loop thru the dim_options and find the option_id for the_dim
						foreach($dim_options as $d_opt)
						{
							if($the_dim == $d_opt["label"])
							{
								$the_dim_id = $d_opt["value"];
								break;
							}
						}

						if($the_dim_id == 0 )
						{
							return "Error in " . __FUNCTION__ . " on line " . __LINE__ . ". Dimension ID was empty. Size is going to be empty as an option.";
						}							    
					}
					$omx2mage_product_data_map[$lname] = $the_dim_id;
				}
				else
				{
					return "Error in " . __FUNCTION__ . " on line " . __LINE__ . ". Attribute array empty.";
				}

			} // end for

			$omx2mage_product_data_map["name"] = $base_name . " " . $sizecolor_description;
			$omx2mage_product_data_map["sku"] = $sub_stocknumber;
			if($sur > .01)
			  {
	    			$omx2mage_product_data_map["price"] += $sur;
			  }

			try
			{
			// this call to info() can throw a "not_exists" exception, if so, catch it and create it
				$info = $prod_api->info($sub_stocknumber);
				if( $info["sku"] == $sub_stocknumber )
				{
					$prod = Mage::getModel('catalog/product');
					$productId = $prod->getIdBySku($sub_stocknumber);
					$prod_api->update($productId, $omx2mage_product_data_map, Mage_Core_Model_App::ADMIN_STORE_ID);
				}
				else
				{
					$productId = $prod_api->create('simple', $default_attribute_set_id, $sub_stocknumber, $omx2mage_product_data_map); 
				}
			} 
			catch(Exception $e) 
			{
				$productId = $prod_api->create('simple', $default_attribute_set_id, $sub_stocknumber, $omx2mage_product_data_map); 
			}

			if($sur > .01)
			  {
	    			$omx2mage_product_data_map["price"] -= $sur;
			  }

			// why are we doing this ??? we need the id's of the product as the key to the array to be returned by reference.
			if( is_numeric($productId) )
			{
				$num_attribute_ids = count($attribute_ids);
				$subitem_ids[$productId] = array();
				for($a=0; $a<$num_attribute_ids; $a++)
				{
					try
					{
						$subitem_ids[$productId][$a] = array('attribute_id' => $attribute_ids[$a]);
					}
					catch( Exception $e )
					{
					   return "Error: " . __FUNCTION__ . " on line " . __LINE__ . " : " .  $e->GetMessage();
					}
				}
			}
			// set the inventory quantity
			$qty = intval($xml->WebStore->ItemData->Item->SubItem[$i]->Available[0]);
			$in_stock = ($qty > 0) ? 1 : 0;
			$product_stock = new Mage_CatalogInventory_Model_Stock_Item_Api();

			$product_stock->update($sub_stocknumber, array('qty' => $qty, 'is_in_stock' => $in_stock));
		}

	}

	/**
	 * massageDimensionData function.
	 * 
	 * @access private
	 * @param mixed $dimensiondata
	 * @param mixed &$variation_theme
	 * @return void
	 */
	private function massageDimensionData($dimensiondata, &$variation_theme) 
	{
		$map = array();
		$variation_theme = "";
		$numdim = count($dimensiondata->Dimension);
		for($i=0; $i<$numdim; $i++)
		{
			$attrs = $dimensiondata->Dimension[$i]->attributes();
			$dimname = $attrs["name"];
			
			// build variation theme from dimension name
			$variation_theme .= ($i > 0) ? "-" . $dimname : $dimname;
			
			$numval = count($dimensiondata->Dimension[$i]->Value);
			for($j=0; $j<$numval; $j++)
			{
				$attrs2 = $dimensiondata->Dimension[$i]->Value[$j]->attributes();
				$valueID = $attrs2["valueID"];
				$key = $dimname . "_" . $valueID;
				$desc = $dimensiondata->Dimension[$i]->Value[$j]->Description[0];
				$surcharge = $dimensiondata->Dimension[$i]->Value[$j]->Surcharge[0];
				
				// save in map
				$map[$key]["Description"] = $desc;
				$map[$key]["Surcharge"] = $surcharge;
			}
		}
		return $map;
	}

	/**
	 * AddMageAttribute function.
	 * 
	 * @access private
	 * @param mixed $code
	 * @param mixed $default_attribute_set_id
	 * @return void
	 */
	private function AddMageAttribute( $code , $default_attribute_set_id)
	{	
		try 
		{
		
			$type_id = 10; //magic number - from eav_attribute.entity_type_id
			$eav_entity_type = new  Mage_Eav_Model_Entity_Type();
			$eav_entity_type->loadByCode("catalog_product");
			$type_id = $eav_entity_type->getEntityTypeId();

			$c = array(
			'entity_type_id' => $type_id,
			'attribute_code' => $code,
			'backend_type'   => 'text',
			'frontend_input' => 'select',
			'is_global'      => '1',
			'is_visible'     => '1',
			'is_required'    => '0',
			'is_user_defined' => '1',
			'position'        => '1',
			'frontend_label' => $code
			);

			//$attribute = new Mage_Eav_Model_Entity_Attribute();
			$attribute   = Mage::getModel('eav/entity_attribute');
			$attribute->loadByCode($c['entity_type_id'], $c['attribute_code'])->setStoreId(0)->addData($c);
			$attribute->save();
			$new_id = $attribute->getId();
			$sql = "insert into eav_entity_attribute (entity_type_id, attribute_set_id, attribute_group_id, attribute_id) values (";
			$sql .= $type_id . "," . $default_attribute_set_id . ",4," . $new_id . ")";

			// fetch write database connection that is used in Mage_Core module
			$write = Mage::getSingleton('core/resource')->getConnection('core_write');
			$write->query( $sql ); 

		} catch (Exception $e) {
			return "Exception in " . __FUNCTION__ . " on line " . __LINE__ . " : " .  $e->GetMessage();
		}
		return $new_id;
	}

	/**
	 * AddProductImage function.
	 * 
	 * @access private
	 * @param mixed $product
	 * @param mixed $fullImagePath
	 * @param mixed $typeofimage
	 * @return void
	 */
	private function AddProductImage( $productId, $fullImagePath, $image_type )
	{
	
		$product = Mage::getModel('catalog/product');
		$product->load($productId);
		//$prod->setTypeId($prod_type);
	
		$image_type = trim($image_type);
		try
		{
		  // check input
			if( !is_object($product) )
			{
				return "product object empty.";
			}
			if(!file_exists($fullImagePath) || !is_file($fullImagePath))
			{
				return $fullImagePath . " image file does not exist";
			}

			$arrayValidImageTypes = array("image", "thumbnail", "small_image");

			if( !in_array($image_type,$arrayValidImageTypes) )
			{
				return "'" . $typeofimage . "'" . " image type not found.";
			}
			
			$bFound = 0;
			
			// no media gallery?
			if( !$product->getMediaGallery () )
			{
				//This call is needed since the media gallery is null for a newly created product.
				$product->setMediaGallery (array('images'=>array (), 'values'=>array ()));
			}
			else
			{
				// get the existing media gallery
				$images = $product->getMediaGalleryImages();
				// see if the one we are trying to add already exists
				foreach ($images as $key=>$image)
				{
					$needle = basename($fullImagePath);
					$needle = substr($needle, 0, strrpos($needle,"."));
					if( false !== strpos($image->getfile(),$needle  ) )
					{
						$bFound = 1;
						break;
					}
				}
			}
			if( !$bFound )
			{
				$product->setMediaGallery (array('images'=>array (), 'values'=>array ()));
				if ($image_type == "image")
				{
					$product->addImageToMediaGallery ($fullImagePath, array('image'), false, false);
				}
				if ($image_type == "small_image")
				{
					$product->addImageToMediaGallery ($fullImagePath, array('small_image'), false, false);
				}
				if ($image_type == "thumbnail")
				{
					$product->addImageToMediaGallery ($fullImagePath, array('thumbnail'), false, false);
				}
				$product->save();
			}
			return "";

		} catch (Exception $e) {
			return "Exception: "  . $e->GetMessage() . " on line " . __LINE__ . ".";			
		}
	}


	/**
	 * syncExistingProduct function.
	 * 
	 * @access private
	 * @param mixed $itemcode
	 * @param mixed $data
	 * @param mixed $default_attribute_set_id
	 * @return void
	 */
	private function syncExistingProduct($itemcode, $data, $default_attribute_set_id)
	{
		global $BizSyncStoreID;

		
		$xml = simplexml_load_string ($data);	
		if (get_class($xml) != "SimpleXMLElement")
		{
			return "Error: (syncExistingProduct) XML invalid and could not parse.";
		}
			
		if($itemcode == "")
		{
			return "Item code missing";
		}

		if($data == "")
		{
			return "Data empty";
		}

		if(!is_numeric($default_attribute_set_id))
		{
			return "Default attribute id not set";
		}

		$item_attributes = $xml->WebStore->ItemData->Item->attributes();
		$stocknumber = $item_attributes['itemCode'];
		if ($stocknumber == '')
		{
			return "XML had no stocknumber";
		}

		if ($stocknumber != $itemcode)
		{
			return "XML stocknumber did not match itemcode";
		}

		$taxable = ($item_attributes['isTaxable']) == "True" ? 1 : 0;
		$is_active = ($item_attributes['active']) == "True" ? 1 : 2;
		$wgt = escapeInventoryFileData($xml->WebStore->ItemData->Item->Weight[0]);
		$row_serialized = '';

		if (trim($xml->WebStore->RowData[0]) != '')
		{
			$row_data = base64_decode (trim($xml->WebStore->RowData[0]));
			$row_array = json_decode(json_encode((array) simplexml_load_string($row_data)),1);
			$row_serialized = serialize ($row_array);
		}
		// map channelbrain to Magento.
		$omx2mage_product_data_map = array(
						'name'              => escapeInventoryFileData($xml->WebStore->ItemData->Item->ProductName[0]),
						'weight'	    => $wgt ? $wgt : 1,
						'websites'          => array(intval($BizSyncStoreID)), //array(1), // array(1,2,3,...)
						'short_description' => escapeInventoryFileData($xml->WebStore->ItemData->Item->ProductName[0]),		
						'description'       => escapeInventoryFileData($xml->WebStore->ItemData->Item->InfoText[0]),
						'price'             =>  str_replace(",","",escapeInventoryFileData($xml->WebStore->ItemData->Item->PriceData->Price->Amount[0])),
						'tax_class_id'	=> '2',
						'meta_rowdata'	=> $row_serialized,
						'status'	=> $is_active);

		$sync_product_longdescription = $_POST['sync_product_longdescription'];
		$sync_product_shortdescription = $_POST['sync_product_shortdescription'];
		$sync_product_name = $_POST['sync_product_name'];

		// let's determine if we're syncing the name and the long description.
		if ($sync_product_longdescription != "1") {
			unset ($omx2mage_product_data_map['description']);
		}
		// let's determine if we're syncing the name and the short description.
		if ($sync_product_shortdescription != "1") {
			unset ($omx2mage_product_data_map['short_description']);		
		}

		$prod_type = "simple";

		// Are we a configurable product with subItems ?
		if( count($xml->WebStore->ItemData->Item->SubItem) > 0 )
		{
			$prod_type = "configurable";
		}

		// go into admin mode
		Mage::app()->getStore()->setId(Mage_Core_Model_App::ADMIN_STORE_ID);
		$prod = Mage::getModel('catalog/product');
		$productId = $prod->getIdBySku($stocknumber);
		$prod->load($productId);

		// let's determine if we're syncing the name and the product name.
		if ($sync_product_name != "1") {
			//unset ($omx2mage_product_data_map['name']);
			$omx2mage_product_data_map['name'] = $prod->getName();
		}	

		if($prod_type == "simple")
		{
			if( $productId > 0 )
			{
				$prod_api =  new Mage_Catalog_Model_Product_Api();
				$prod_api->update($productId, $omx2mage_product_data_map, Mage_Core_Model_App::ADMIN_STORE_ID);
			}
			else
			{
				return "Product id was invalid";
			}
		
			// set the inventory quantity
			$qty = intval($xml->WebStore->ItemData->Item->Available[0]);
			$in_stock = ($qty > 0) ? 1 : 0;
		
			$product_stock = new Mage_CatalogInventory_Model_Stock_Item_Api();
			$product_stock->update($productId, array('qty' => $qty, 'is_in_stock' => $in_stock));
		}

		// init configurable product and handle sub items
		if( $prod_type == "configurable" )
		{
			$attribute_ids = array();
			$subitem_ids   = array();
			$subitem_skus   = array();

			// do subitems...
			if( count($xml->WebStore->ItemData->Item->SubItem) > 0 )
			{
				$this->DoSubItems($xml, $default_attribute_set_id, $omx2mage_product_data_map, $itemcode, $attribute_ids, $subitem_ids, $subitem_skus);
			}

			if( $productId > 0 )
			{
				$prod_api =  new Mage_Catalog_Model_Product_Api();
				$omx2mage_product_data_map["associated_skus"] = $subitem_skus;
				$prod_api->update($productId, $omx2mage_product_data_map, 1);
			}
			else
			{
				return "Product id was invalid";
			}

			try
			{
				//$prod->load($productId);
				$prod->setTypeId($prod_type);
				$prod->setConfigurableProductsData($subitem_ids) ;
				$prod->getTypeInstance()->save();
			} catch(Exception $e) {
				  return "Exception in " . __FUNCTION__ . " on line " . __LINE__ . " : " .  $e->GetMessage();
			}
		}


		// make sure prod is initialized
		if( !is_object($prod) )
		{
			$prod = Mage::getModel('catalog/product');
			$prod->load($productId);
			$prod->setTypeId($prod_type);
		}

		if( is_numeric( $productId ) )
		{

			if( count($xml->WebStore->ItemData->Item->ImageData->Image) > 0 )
			{
				$numAD = count($xml->WebStore->ItemData->Item->ImageData->Image);
				for($k=0; $k<$numAD; $k++)
				{
					$attr = $xml->WebStore->ItemData->Item->ImageData->Image[$k]->attributes();				
					if( strtolower($attr["type"]) == "image" ||
					    strtolower($attr["type"]) == "thumbnail" ||
					    strtolower($attr["type"]) == "small_image")
					{
						$filename = $attr['filename'];
						$image_type = trim($attr["type"]); // image, small_image, or thumbnail
						$fullImagePath = Mage::getBaseDir('media') . "/import/" . $filename;
						if ($attr['image_data'] != "")
						{
							// always overwrite the file.
							file_put_contents($fullImagePath, base64_decode (trim($attr['image_data'])));
							if(file_exists($fullImagePath) && is_file($fullImagePath))
							{
								$result = AddProductImage( $productId, $fullImagePath, $image_type );
							}
						} 
					}
				}
			}
		}

		// add tier pricing
		if( is_numeric( $productId ) && count($xml->WebStore->ItemData->Item->PriceData->Price) > 0 )
		{
			$mcmpata = new Mage_Catalog_Model_Product_Attribute_Tierprice_Api();
			$tierprices = array();
			$numPr = count($xml->WebStore->ItemData->Item->PriceData->Price);
			for($m=0; $m<$numPr; $m++)
			{
				$attr = $xml->WebStore->ItemData->Item->PriceData->Price[$m]->attributes();
				$quan = intval($attr["quantity"]);
				$bMultiplier = $attr["multiplier"];
				$amount = floatval($xml->WebStore->ItemData->Item->PriceData->Price[$m]->Amount[0]);
				$SH = floatval($xml->WebStore->ItemData->Item->PriceData->Price[$m]->AdditionalSH[0]);
				$tierprices[] = array('website'           => 'all',
						      'customer_group_id' => 'all',
						      'qty'               => $quan,
						      'price'             => $amount);
			}
			try
			{
				$mcmpata->update($productId, $tierprices);
			} catch(Exception $e) {
				return  "Error in " . __FUNCTION__ . " on line " . __LINE__ . " adding tier price for " . $itemcode . ". " . $e->GetMessage(); 
			}
		}
		return true;		
	}


	/**
	 * SetWebsite function
	 *
	 * @return void
	 * @author Gary MacDougall
	 **/
	private function SetWebsite ($stocknumber)
	{
		Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
		$product = Mage::getModel('catalog/product');
		$productId = $product->getIdBySku($stocknumber);
		$prod_api =  new Mage_Catalog_Model_Product_Api();
		$prod_attrs['websites'] = array ($storeCode);
		$prod_api->update ($productId,$prod_attrs);
		umask(0);
	}
   
	/**
	 * GetSKU function
	 *
	 * @return void
	 * @author Gary MacDougall
	 **/
	private function GetSKU()
	{
		$sku = $this->ActionValues['sku'];
		if (!isset($sku))
		{
			$sku = $_REQUEST['sku'];
		}	
		return $sku;
	}
	
	/**
	 * undocumented function
	 *
	 * @return void
	 * @author Gary MacDougall
	 **/
	public function GetModuleVersion()
	{
		return MODULE_BASE_VERSION;
	}

}; // END class 
?>