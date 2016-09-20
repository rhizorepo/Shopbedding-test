<?php

class BorderJump_BorderShip_Model_Carrier_Bordership
    extends Mage_Shipping_Model_Carrier_Abstract
{
    
    /**
     * unique internal shipping method identifier
     *
     * @var string [a-z0-9_]
     */
    protected $_code = 'bordership';
    
    // works with any magento object that implements getAdditionalData and setAdditionalData
    public function setAdditionalData($object, $newData) {
        if ($newData === null) {
            $object->setAdditionalData(null);
        }
        
        $data = $object->getAdditionalData();
        
        // if there's no additional data, create the array()
        if ( ! $data) {
            $data = array('bordership' => array());
        } else {
            // attempt to convert the data from json to an array
            $data = json_decode($data, true);
            
            // if the conversion failed
            if ($data == null || ! is_array($data)) {
                return false;
            }
            
            // if the data doesn't have the borderpay key, create it
            if ( ! isset($data['bordership'])) {
                $data['bordership'] = array();
            }
        }
        
        // finally add the new data
        $data['bordership'] = array_merge($data['bordership'], $newData);
        
        // serialize and set
        try {
            $object->setAdditionalData(json_encode($data));
        } catch (Exception $e) {
            return false;
        }
        return true;
    }
    
    public function getAdditionalData($object, $key=null) {
        $data = $object->getAdditionalData();
        
        if ( ! $data) {
            return false;
        } else {
            // attempt to convert the data from json to an array
            $data = json_decode($data, true);
            
            // if the conversion failed
            if ($data == null || ! is_array($data)) {
                return false;
            }
            
            // if the data doesn't have the borderpay key
            if ( ! isset($data['bordership'])) {
                return false;
            }
        }
        
        if ($key) {
            if (isset($data['bordership'][$key])) {
                return $data['bordership'][$key];
            } else {
                return false;
            }
        } else {
            return $data['bordership'];
        }
    }
    
    public function apiQuotes(Mage_Shipping_Model_Rate_Request $request) {
        Mage::getSingleton('core/session', array('name' => 'frontend'));
        $session = Mage::getSingleton('checkout/session');
        
        $items = $request->getData('all_items');
        $address = $items[0]->getQuote()->getShippingAddress();
        
        $response = null;
        if ($address->getData('country_id') and $this->getConfigData('api_url')) {
            
            /* Begin building the commodities array */
            $commodities = $this->getApiCommodities($request);
            $toAddress = $this->getApiToAddressFromAddress($address);
            
            $requestData = array(
                'commodities' => $commodities,
                'destinationAddress' => $toAddress
            );
            
            $responseBody = $this->getApiClient()->call('/quotes', 'POST', $requestData);
        }
        
        $session->setBordershipQuotesAddress($toAddress);
        $session->setBordershipQuotesCommodities($commodities);
        return $responseBody['response'];
    }
    
    /**
    * Collect rates for this shipping method based on information in $request.
    * 
    * @param Mage_Shipping_Model_Rate_Request $data
    * @return Mage_Shipping_Model_Rate_Result
    */
    public function collectRates(Mage_Shipping_Model_Rate_Request $request) {
        
        // stop if the module is not active.
        if (!$this->getConfigFlag('active')) {
            return false;
        }
        
        // stop if the terms url hasn't been set.
        if (!$this->getConfigData('terms_url')) {
            return false;
        }
        
        $responseBody = $this->apiQuotes($request);
        $statusCode = $this->getApiClient()->getLastResponse()->getStatus();
        
        $result = Mage::getModel('shipping/rate_result');
        
        if ($statusCode == 201 && $responseBody) {
            Mage::getSingleton('core/session', array('name' => 'frontend'));
            $session = Mage::getSingleton('checkout/session');
            $session->setBordershipBjlOrderNumber($responseBody['bjlOrderNumber']);
            try {
                $domesticPrice = $this->_getTableRate($request);
            } catch (Exception $e) {
                $domesticPrice = 0;
            }
            foreach ($responseBody['shippingMethods'] as $responseMethod) {
                $methodTitle = $this->_buildMethodTitle(
                    $responseMethod['name'], $responseMethod['minDays'], $responseMethod['maxDays']
                );
                $method = Mage::getModel('shipping/rate_result_method');
                $method->setCarrier('bordership');
                $method->setCarrierTitle($this->getConfigData('title'));
                $method->setMethod($responseMethod['shippingMethodId']);
                $method->setMethodTitle($methodTitle);
                $price = $responseMethod['costComponents']['price'] += $domesticPrice;
                if ($request->getFreeShipping() === true || $request->getPackageQty() == $this->getFreeBoxes()) {
                    $price = '0.00';
                }
                $method->setPrice($price);
                $method->setCost($price);
                $result->append($method);
            }
        } else {
            Mage::log('Could not get BorderShip shipping methods');
        }
        return $result;
    }
    
    /**
     * Create the full shipping method title in the format:
     * [method name] ([minimum ship days + merchant handling time] - [max ship days + merchant handling time] days)
     * 
     * @param string $name
     * @param string|int $minDays Should be int-like if it's a string
     * @param string|int $maxDays Should be int-like if it's a string
     */
    private function _buildMethodTitle($name, $minDays, $maxDays) {
        $handlingTime = $this->getConfigData('domestic_shipping_estimate');
        if (is_numeric($handlingTime)) {
            $minDays += $handlingTime;
            $maxDays += $handlingTime;
        }
        return "$name ($minDays - $maxDays days)";
    }
    
    public function apiParcelCancel($parcelNumber) {
        $requestBody = array('bjlParcelNumber' => $parcelNumber);
        $responseBody = $this->getApiClient()->call('/parcels', 'DELETE', $requestBody);
        
        //~ // Response should be blank. If it's false, or it has a body, something went wrong.
        //~ if ($response != null) {
            //~ Mage::throwException(Mage::helper('bordership')->
                //~ __('Could not delete tracking number with BorderShip.'));
        //~ }
        if (! empty($responseBody['response'])) {
            return $responseBody['response'];
        }
        return null;
    }
    
    public function apiParcelRead($track) {
        $bsTrack = Mage::getModel('bordership/track')->loadByMageTrack($track);
        $request = array('bjlParcelNumber' => $bsTrack->getInboundParcelNumber());
        $responseBody = $this->getApiClient()->call('/parcel', 'PUT', $request);
        $status = $this->getApiClient()->getLastResponse()->getStatus();
        return $responseBody['response'];
    }
    
    public function apiParcel($track) {
        
        // get the shipment
        $shipment = $track->getShipment();
        
        // get the order
        $order = $shipment->getOrder();
        
        // get the bordership order
        $bsOrder = Mage::getModel('bordership/order')->loadByMageOrder($order);
        
        // get order carrier--should be bordership
        $carrier = $order->getShippingCarrier();
        
        // get total weight
        $totalWeight = 0;
        foreach ($this->getAllVisibleItems($shipment) as $item) {
            $totalWeight += $item->getWeight() * $item->getQty();
        }
        
        $requestBody = array(
            'bjlOrderNumber' => $bsOrder->getOrderNumber(),
            'customIdentifier' => $track->getEntityId(),
            'domesticCarrierName' => $track->getTitle(),
            'domesticCarrierTrack' => $track->getNumber(),
            'commodities' => $carrier->getApiCommodities($shipment, false),
            'size' => array(
                'weight' => ceil($totalWeight),
                'width' => 0,
                'length' => 0,
                'height' => 0,
                'volume' => 0,
                'source' => 'Magento'
            )
        );
        
        $responseBody = $this->getApiClient()->call('/parcels', 'POST', $requestBody);
        $status = $this->getApiClient()->getLastResponse()->getStatus();
        if ($status != '201') {
            if ($this->getApiClient()->getError() === 'bjlOrderNumber not ready for shipment') {
                Mage::throwException(Mage::helper('bordership')->__('This order is not ready for shipment.'));
            }
            Mage::throwException(Mage::helper('bordership')->
                __('Could not create tracking number: the parcel pre-alert could not be created with BorderShip.'));
        }
        
        //~ $bsTrack = Mage::getModel('bordership/track');
        //~ $bsTrack->setMageTrack($track);
        //~ $bsTrack->setInboundParcelNumber($response['inbound_parcel_number']);
        //~ $bsTrack->setParcelIdentificationNumber($parcelIdentificationNo);
        //~ $bsTrack->save();
        
        return $responseBody['response'];
    }
    
    public function apiTrackRead($numbers) {
        $requestBody = array(
            'trackNumbers' => $numbers
        );
        $responseBody = $this->getApiClient()->call('/tracks', 'GET', $requestBody);
        $status = $response = $this->getApiClient()->getLastResponse();
        if ($status == '200') {
            return $responseBody;
        }
        return null;
    }
    
    public function apiOrderShipto($order) {
        $bsOrder = Mage::getModel('bordership/order')->loadByMageOrder($order);
	$bjlOrderNumber = $bsOrder->getOrderNumber();
	if($bjlOrderNumber) {
	        $requestBody = array('bjlOrderNumber' => $bjlOrderNumber);
	        $responseBody = $this->getapiClient()->call('/orders/hub', 'GET', $requestBody);
        	return $responseBody['response'];
	} else {
		return null;
	}
    }
    
    public function apiOrder($order) {
        Mage::getSingleton('core/session', array('name' => 'frontend'));
        $session = Mage::getSingleton('checkout/session');
        $bjlOrderNumber = $session->getBordershipBjlOrderNumber();
        
        $commodities = $this->getApiCommodities($order);
        $address = $this->getApiToAddressFromAddress($order->getShippingAddress());
                
        $requestBody = array(
            'bjlOrderNumber' => $bjlOrderNumber,
            'customIdentifier' => $order->getIncrementId(),
            'destinationAddress' => $address,
            'commodities' => $commodities,
            'deliveryOption' => array(
                'shippingMethodId' => substr($order->getShippingMethod(), 11)
            )
        );
        
        $responseBody = $this->getApiClient()->call('/orders', 'POST', $requestBody);
        $response = $this->getApiClient()->getLastResponse();
        
        return $responseBody['response'];
    }
    
    public function apiOrderRead($order) {
        $bordershipOrder = Mage::getModel('bordership/order')->loadByMageOrder($order);
        $bjlOrderNumber = $bordershipOrder->getBjlOrderNumber();
        $requestBody = array('bjlOrderNumber' => $bjlOrderNumber);
        if ($bjlOrderNumber) {
            $responseBody = $this->getApiClient()->call('/orders', 'GET', $requestBody);
            return $responseBody['response'];
        }
        return null;
    }

    private function _getPendingOrders() {
        $orders = Mage::getModel('sales/order')->getCollection();
        $orders->addAttributeToFilter('status', array('eq' => 'pending_borderjump'));
        $orders->getSelect();
        return $orders;
    }
    
    public function apiOrderStatus() {
	// Get all orders with a status of pending_borderjump
	$orders = $this->_getPendingOrders();

        $bjlOrderNumbers = array();

	for($i=0; $i<count($orders); $i++)
        {   
            $bjlOrderNumbers['order_numbers'][$i] = $orders[$i];
	}   

        if ($bjlOrderNumbers) {
            $requestBody = $bjlOrderNumbers;
            $responseBody = $this->getApiClient()->call('/orders/magento_status', 'GET', $requestBody);
            return $responseBody['response'];
        }
        return null;
    }
    
    public function updateAllPendingOrders() {

	// Check the status of those orders
	$result = $this->apiOrderStatus();

 	    // Send those orders to Ship to see which ones are ready to go
	    $results = json_decode($result, true);
 	    $bjlOrdersArray = $results['response']['orders'];

	    foreach ($bjlOrdersArray as $key => $value){
	        if($value===true) {
		        Mage::log("BJL: Updating status for ".$key);
	    	        $magentoOrder = $observer->getOrder();
		        $magentoOrder->setStatus('processing');
		        $magentoOrder->save();
                }
	    }
    }

    public function apiOrderCancel($order) {
        $bordershipOrder = Mage::getModel('bordership/order')->loadByMageOrder($order);
        $bjlOrderNumber = $bordershipOrder->getOrderNumber();
        $requestBody = array('bjlOrderNumber' => $bjlOrderNumber);

        if ($bjlOrderNumber) {
            $responseBody = $this->getApiClient()->call('/orders', 'DELETE', $requestBody);
            return $responseBody['response'];
        }
        return null;
    }
    
    protected function _getTableRate($object) {
        $totalWeight = $this->_getTotalWeight($object);
        
        $tableRate = Mage::getModel('bordership/carrier_tablerate');
        $price = $tableRate->getRate($totalWeight);
        return $price;
    }
    
    protected function _getTotalWeight($object) {
        $items = null;
        
        if ($object instanceof Mage_Sales_Model_Quote) {
            $items = $object->getAllVisibleItems();
        } else {
            $items = $object->getAllItems();
        }
        
        $totalWeight = 0;
        foreach($items as $item) {
            $totalWeight += $item->getWeight() * $item->getQty();
        }
        return $totalWeight;
    }
    
    public function isTrackingAvailable() {
        return false;
    }
    
    /**
     * Gets an address array for a json query from a magento address
     * object.
     */
    public function getApiToAddressFromAddress($address) {
        //~ var_dump($address->getData());die();
        $toAddress = array();
        $toAddress['firstName'] = $address->getData('firstname') or $toAddress['firstName'] = 'NO_DATA';
        $toAddress['lastName'] = $address->getData('lastname') or $toAddress['lastName'] = 'NO_DATA';
        $toAddress['street1'] = $address->getData('street') or $toAddress['street1'] = 'NO_DATA';
        $toAddress['city'] = $address->getData('city') or $toAddress['city'] = 'NO_DATA';
        $toAddress['region'] = $address->getRegionCode() or $toAddress['region'] = 'NO_DATA';
        $toAddress['country'] = $address->getData('country_id');
        $toAddress['postalCode'] = $address->getData('postcode') or $toAddress['postalCode'] = 'NO_DATA';
        $toAddress['phoneNumber1'] = $address->getData('telephone') or $toAddress['phoneNumber1'] = 'NO_DATA';
        $toAddress['email'] = $address->getData('email') or $toAddress['email'] = 'no_data@example.com';
        return $toAddress;
    }
    
    public function getAllVisibleItems($object) {
        $items = array();
        foreach ($object->getAllItems() as $item) {
            if (method_exists($item, 'getOrderItem')) {
                $hasParent = $item->getOrderItem()->getParentItemId() ? true : false;
            } else {
                $hasParent = $item->getParentItemId() ? true : false;
            }
            if (! $hasParent) {
                $items[] =  $item;
            }
        }
        return $items;
    }
    
    public function getApiCommoditiesWithWeights($object) {
        $commodities = array();
        $items = $object->getAllItems();
        
        foreach($items as $item) {
            $product = $item->getProduct();
            if (! $product) {
                $isVirtual = $item->getOrderItem()->getIsVirtual();
            } else {
                $isVirtual = $product->isVirtual();
            }
            
            if ($isVirtual) {
                continue;
            }
            $size = array(
                "weight" => (float) $item->getWeight(),
                "width" => 0,
                "length" => 0,
                "height" => 0,
                "volume" => 0,
                "source" => "Magento"
            );
            
            $commodity = array();
            $commodity['SKU'] = $item->getSku();
            $commodity['quantity'] = (integer) $item->getQty();
            $commodity['size'] = $size;
            $commodities[] = $commodity;
        }
        return $commodities;
    }
    
    protected function _getQty($item) {
        $qty = $item->getQtyOrdered();
        if (! $qty) {
            $qty = $item->getQty();
        }
        if (! $qty) {
            $qtyOptions = $item->getQtyOptions();
            $qty = $qtyOptions['qty'];
        }
        return (int) $qty;
    }
    
    protected function _randomString($length) {
        $str = '';
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $size = strlen( $chars );
        for($i = 0; $i < $length; $i++) {
            $str .= $chars[rand(0, $size - 1)];
        }
        return $str;
    }
    
    public function makeBorderjumpOrderId($useSession = false) {
        $orderNumber = '';
        
        if ($useSession) {
            Mage::getSingleton('core/session', array('name' => 'frontend'));
            $session = Mage::getSingleton('checkout/session');
            $bpPayment = $session->getBorderpayPayment();
            if ($bpPayment) {
                $orderNumber = $bpPayment->getOrderNumber();
            }
        }
        
        if (! $orderNumber) {
            $accountNumber = Mage::getStoreConfig('carriers/bordership/account_number');
            $accountNumber = substr($accountNumber, -3, 3);
            $orderNumber = $accountNumber . $this->_randomString(8);
        }
        return $orderNumber;
    }
    
    /**
     * Gets the commodities array for a json query from a magento order
     * object, or any object with a getAllVisibleItems method.
     */
    public function getApiCommodities($object, $withPrices = true) {
        $commodities = array();
        $items = $object->getAllItems();

        foreach($items as $item) {
		Mage::log(get_class($item));
            if (get_class($item) != 'Mage_Sales_Model_Order_Shipment_Item' && $item->getProduct()->isVirtual()) {
		Mage::log("Skipping virtual product.");
                continue;
            }

	// Set bjlItemPrice to be used when checking for 0.00 simple products
	switch (get_class($item)) {
	    case "Mage_Sales_Model_Order_Item":
	        $bjlItemPrice = sprintf('%01.2f', $item->getBasePrice());
		break;

	    case "Mage_Sales_Model_Order_Shipment_Item":
	        $bjlItemPrice = sprintf('%01.2f', $item->getBasePrice());
		break;
	
	    default:
		$bjlItemPrice = sprintf('%01.2f', $item->getPrice());
	}


	    // If this is being called during an order check for 0.00 prices, otherwise it's inbound parcel and this should be skipped
            if (get_class($item) != 'Mage_Sales_Model_Order_Shipment_Item') {
	        // If product is simple and $0.00 (child of configurable product) skip over it
	        if ($item->getProduct()->getTypeId()=="simple" && $bjlItemPrice=="0.00") {
	   	    Mage::log("Skipping simple product with 0.00 price.");
		    continue;
	        }
	    } else {
		// For inbound parcel skip the type check and skip all prices of 0.00
	        if ($bjlItemPrice=="0.00") {
		    Mage::log("Skipping simple product with 0.00 price.");
		    continue;
		}
	    }

            $commodity = array(
                'sku' => $item->getSku(),
                'name' => $item->getName(),
                'quantity' => $this->_getQty($item)
            );

            if ($withPrices) {
		    $commodity['price'] = $bjlItemPrice;
	    }

            $commodities[] = $commodity;
        }
        return $commodities;
    }
    
    public function getTrackingInfo($tracking) {
        $result = $this->getTracking($tracking);
        
        if($result instanceof Mage_Shipping_Model_Tracking_Result){
            if ($trackings = $result->getAllTrackings()) {
                return $trackings[0];
            }
        }
        
        elseif (is_string($result) && !empty($result)) {
            return $result;
        }

        return false;
    }
    
    /* 
     * This is where the tracking info gets sent to the template.
     * We'll need to do the API calls here to get the tracking info
     * from BorderJump.
     */
    public function getTracking($trackingNumber) {
        
        /*
        $jsonRequest = array (
            'identifiers' => array(
                    $trackingNumber
            )
        );
        
        $key = $this->getConfigData('api_key');
        $secret = $this->getConfigData('api_secret');
        
        $apiClient = Mage::getModel('BorderJump_ApiClient_Model_ApiClient',
                        $this->getConfigData('api_url'));
        $apiClient->setKey($key);
        $apiClient->setSecret($secret);
                        
        $response = $apiClient->call('/package/track', 'POST', $jsonRequest);
        */
        
        $result = Mage::getModel('shipping/tracking_result');
        
        $tracking = Mage::getModel('shipping/tracking_result_status');
        $tracking->setTracking($trackingNumber);
        $tracking->setCarrier('bordership');
        $tracking->setUrl("http://track.borderjump.com/?track_id=$trackingNumber");
        $tracking->setCarrierTitle($this->getConfigData('title'));
        //$tracking->addData(array('status' => 'test'));
        
        /*
        if ($response) {
            if ($response['errors']) {
                $tracking->setErrorMessage();
            }
        } else {
            $tracking->setErrorMessage('Could not retrieve tracking information.');
            }
        } else {
            $tracking->setErrorMessage('Could not retrieve tracking information.');
        }
        */
        
        $result->append($tracking);
        
        return $result;
    }
    
    protected function getApiClient() {
        if (! $this->apiClient) {
            $this->apiClient = Mage::getModel('apiclient/apiclient', $this->getConfigData('api_url'));
            $this->apiClient->setKey($this->getConfigData('api_key'));
            $this->apiClient->setSecret($this->getConfigData('api_secret'));
        }
        return $this->apiClient;
    }
}
