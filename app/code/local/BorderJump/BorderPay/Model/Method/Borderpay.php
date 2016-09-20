<?php

class BorderJump_BorderPay_Model_Method_BorderPay extends Mage_Payment_Model_Method_Abstract
{
    protected $apiClient = null;
    protected $_code = 'borderpay';
    protected $_canSaveCc = true;
    protected $_canAuthorize = true;
    protected $_formBlockType = 'BorderJump_BorderPay_Block_Form_Dynamic';
    protected $_infoBlockType = 'BorderJump_BorderPay_Block_Info_Dynamic';
    
    //~ public function validate() {
        //~ $response = $this->apiMerchantOrderAuth();
        //~ if ($response['PAResStatus'] == 'Y') {
            //~ return $this;
        //~ } else {
            //~ Mage::throwException('There was an error validating your payment. Please contact customer support.');
        //~ }
    //~ }

    private function _getIpAddress() 
    {
        $ip = '';
        if (!empty($_SERVER['HTTP_CLIENT_IP'])){
            $ip=$_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip=$_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip=$_SERVER['REMOTE_ADDR'];
        }
        
        return $ip;
    }
    
    // We're overriding this method to use the shipping address rather than the billing address.
    public function canUseForCountry($country) {
        // allowspecific == 1 means that it can only be used in certain countries
        if ($this->getConfigData('sallowspecific') == 1) {
            Mage::getSingleton('core/session', array('name' => 'frontend'));
            $session = Mage::getSingleton('checkout/session');
            $country = $session->getQuote()->getShippingAddress()->getCountry();
            
            $availableCountries = explode(',', $this->getConfigData('specificcountry'));
            if (!in_array($country, $availableCountries)) {
                return false;
            }
        }
        return true;
    }
    
    public function saveAddresses() {
        Mage::getSingleton('core/session', array('name' => 'frontend'));
        $session = Mage::getSingleton('checkout/session');
        $billing = $session->getQuote()->getBillingAddress()->getData();
        $shipping = $session->getQuote()->getShippingAddress()->getData();
        
        $billing['address'] = $billing['street'];
        $shipping['address'] = $shipping['street'];
        $session->setBorderpayBillingAddress($billing);
        $session->setBorderpayShippingAddress($shipping);
    }
    
    public function validate() {
        parent::validate();
        return $this;
    }
    
    public function getCcAvailableTypes() {
        return array(
            //'American Express' => 'amex',
            'Visa' => 'visa',
            'MasterCard' => 'master',
            'Discover' => 'discover',
            //'Diner\'s Club' => 'diners_club',
            //'JCB' => 'jcb'
        );
    }
    
    public function apiMerchantPaymentServices() {
        $currencyCode = Mage::app()->getStore()->getCurrentCurrencyCode();
        $currencyNum = $this->getIso4217CurrencyCode($currencyCode);
        
        $address = Mage::getSingleton('checkout/session')->getQuote()->getBillingAddress();
        #$billing_address = $customer->getDefaultBillingAddress();
        if ($address) {
            
            $countryCode = $address->getCountry();
            
            $apiClient = Mage::getModel('apiclient/apiclient', $this->getConfigData('api_url'));
            $apiClient->setKey($this->getConfigData('api_key'));
            $apiClient->setSecret($this->getConfigData('api_secret'));
            
            $query = array(
                'currency_num' => $currencyNum,
                'country_code' => $countryCode
            );
            $response = $apiClient->call('/merchant/payment-services/', 'POST', $query);

            return $response;
        } else {
            return false;
        }
    }
    
    private function _getPendingOrders() {
        $orders = Mage::getModel('sales/order')->getCollection();
        $orders->addAttributeToFilter('status', array('eq' => 'pending'));
        $orders->getSelect();
        return $orders;
    }
    
    public function updateAllPendingOrders($real=false)
    {
        if (! $real) {
            print 'Dry run...<br /><br />';
        }
        $orders = $this->_getPendingOrders();
        $borderpayOrders = array();
        
        $orderNumbers = array();
        foreach ($orders as $order) {
            $payment = $order->getPayment();
            $bpPayment = Mage::getModel('borderpay/payment')->loadByMagePayment($payment);
            
            // We don't care if it's not a borderpay order
            if (strtolower($payment->getMethodInstance()->getCode()) != 'borderpay') {
                if (! $real) {
                    print 'Skipping order #' . $order->getIncrementId() .': doesn not appear to be a borderpay order.<br />';
                }
                continue;
            }
            
            // We don't care if it doesn't have borderpay data
            if (! $bpPayment) {
                if (! $real) {
                    print 'Skipping order #' . $order->getIncrementId() .': no borderpay data.<br />';
                }
                continue;
            }
            
            // We can't do anything if there's no order number
            if (! $bpPayment->getOrderNumber()) {
                if (! $real) {
                    print 'Skipping order #' . $order->getIncrementId() .': no order number.<br />';
                }
                continue;
            }
            
            // We don't care if it's already been processed through borderpay
            if ($bpPayment->getProcessed()) {
                
                if (! $real) {
                    print 'Skipping order #' . $order->getIncrementId() .': already processed.<br />';
                }
                continue;
            }
            
            if (! $real) {
                print 'Bingo! Would update order #' . $order->getIncrementId();
            }
            
            $orderNumbers[] = $bpPayment->getOrderNumber();
        }
        
        if (! $real) {
            print '<br />request...<br />';
            var_dump($orderNumbers);
        }
        $response = $this->apiMerchantOrderShipmentStatuses($orderNumbers);
        if (! $real) {
            print '<br />response...<br />';
            var_dump($response);
        }
        
        foreach ($response as $r) {
            $number = $r['order_number'];
            $status = $r['shipment_status'];
            $comment = $r['status_comment'];
            $bpPayment = Mage::getModel('borderpay/payment')->loadByOrderNumber($number);
            $order = $bpPayment->getMageOrder();
            
            if (! $real) {
                print $order->getIncrementId() . "($number): $status";
                continue;
            }
            
            if ($status == 'authorized') {
                $bpPayment->setProcessed(1);
                $bpPayment->save();
                $order->setState(Mage_Sales_Model_Order::STATE_PROCESSING, true);
                $order->save();
            } elseif ($status == 'declined') {
                $bpPayment->setProcessed(1);
                $bpPayment->save();
                $order->setState(Mage_Sales_Model_Order::STATE_CANCELED, true, 'payment declined by BorderJump', true);
                $order->save();
            } elseif ($status == 'pending') {
                // do nothing
            }
        }
    }
    
    public function updatePendingOrder($id) {
        // stub
    }
    
    public function apiMerchantOrderShipmentStatuses($orderNumbers) {
        if (! isset($orderNumbers['order_numbers'])) {
            $request = array('order_numbers' => $orderNumbers);
        } else {
            $request = $orderNumbers;
        }
        
        Mage::log('BEGINNING merchant/order/shipment_statuses');
        Mage::log($request);
        
        $borderpay = Mage::getSingleton('borderpay/method_borderpay');
        $apiClient = Mage::getModel('apiclient/apiclient', $this->getConfigData('api_url'));
        $apiClient->setKey($borderpay->getConfigData('api_key'));
        $apiClient->setSecret($borderpay->getConfigData('api_secret'));
        
        $path = '/merchant/order/shipment_statuses';
        
        $response = $apiClient->call($path, 'POST', $request);
        
        Mage::log($response);
        Mage::log('ENDING merchant/order/shipment_statuses');
        return $response;
    }
    
    public function apiMerchantOrderStatus($orderNumber) {
        Mage::log('BEGINNING merchant/order/status');
        
        $borderpay = Mage::getSingleton('borderpay/method_borderpay');
        $apiClient = Mage::getModel('apiclient/apiclient', $this->getConfigData('api_url'));
        $apiClient->setKey($borderpay->getConfigData('api_key'));
        $apiClient->setSecret($borderpay->getConfigData('api_secret'));
        
        $path = '/merchant/order/status';
        $params = "order_number=$orderNumber";
        //~ $response = $apiClient->call("/merchant/order/status", 'GET', $request);
        $response = $apiClient->call("$path?$params", 'GET', array());
        
        Mage::log($response);
        Mage::log('ENDING merchant/order/status');
        return $response;
    }
    
    public function apiMerchantOrderAuth() {
        Mage::getSingleton('core/session', array('name' => 'frontend'));
        Mage::log('BEGINNING merchant/order/auth');
        
        $post = Mage::app()->getRequest()->getPost();
        $session = Mage::getSingleton('checkout/session');
        $sessionPaymentInfo = $session->getBorderpayPaymentInfo();
 
        if (isset($post['PaRes']) && $post['PaRes']) {
            $payload = $post['PaRes']; 
        } else {
            Mage::log('AUTH ERROR: could not get payload');
            return false;
        }
        
        if ($session->getBorderpayTransactionId()) {
            $transactionId = $session->getBorderpayTransactionId(); 
        } else {
            Mage::log('AUTH ERROR: could not get transaction ID');
            return false;
        }
        
        if ($session->getBorderpayTransactionType()) {
            $transactionType = $session->getBorderpayTransactionType(); 
        } else {
            Mage::log('AUTH ERROR: could not get transaction type');
            return false;
        }
        
	// Set payment data
        if (isset($post['payment']) && $post['payment']) {
            $paymentData = $post['payment'];
        } elseif (isset($sessionPaymentInfo['payment'])) {
            $paymentData = $sessionPaymentInfo['payment'];
        } else {
            $paymentData = array(
                'transaction_type' => '',
                'service' => '',
                'cc_type' => '',
                'cc_number' => '',
                'cc_exp_month' => '',
                'cc_exp_year' => '',
                'cc_cid' => ''
            );
        }

	// Different fields for different payment types
	if($transactionType=="CC") {
	        $query = array(
        	    'PAResPayload' => $payload,
	            'transaction_id' => $transactionId,
        	    'transaction_type' => $transactionType,
	            'cc_type' => isset($paymentData['cc_type']) ? $paymentData['cc_type'] : '',
        	    'cc_number' => isset($paymentData['cc_number']) ? $paymentData['cc_number'] : '',
  	            'expiration_month' => isset($paymentData['cc_exp_month']) ? sprintf('%02s', $paymentData['cc_exp_month']) : '',
        	    'expiration_year' => isset($paymentData['cc_exp_year']) ? $paymentData['cc_exp_year'] : '',
  	            'cc_verification' => isset($paymentData['cc_cid']) ? $paymentData['cc_cid'] : ''
        	);
	} else {

	        $query = array(
        	    'PAResPayload' => $payload,
	            'transaction_id' => $transactionId,
        	    'transaction_type' => $transactionType
		);
	}

        Mage::log($query);
        
        $borderpay = Mage::getSingleton('borderpay/method_borderpay');
        $apiClient = Mage::getModel('apiclient/apiclient', $this->getConfigData('api_url'));
        $apiClient->setKey($borderpay->getConfigData('api_key'));
        $apiClient->setSecret($borderpay->getConfigData('api_secret'));
        
        $response = $apiClient->call('/merchant/order/auth', 'POST', $query);
        Mage::log($response);
        Mage::log('ENDING merchant/order/auth');
        return $response;
    }
    
    public function createOrderNumber() {
        $number = Mage::getModel('bordership/carrier_bordership')->makeBorderjumpOrderId();
        return $number;
    }
    
    private function _getBorderpayCommodities($object) {
        $commodities = array();
       	$invalidChar = "/[^a-zA-Z0-9_ %\[\]\.\,\(\)%&-]/s";
 
        $items = null;
        if (is_subclass_of($object, 'Mage_Sales_Model_Quote')) {
            $items = $object->getAllItems();
        } else {
            $items = $object->getAllItems();
        }
        $toCurrency = Mage::app()->getStore()->getCurrentCurrencyCode();
        $fromCurrency = Mage::app()->getStore()->getBaseCurrencyCode();
        foreach($items as $item) {
            $product = $item->getProduct();
            $product->load($product->getEntityId());
            
	    // If product is simple and $0.00 (child of configurable product) skip over it
            if ($product->getTypeId()=="simple" && sprintf('%01.2f', $item->getPrice())=="0.00") {
                Mage::log("Skipping simple product with 0.00 price.");
                continue;
            }

            $commodity = array(
                'name' => preg_replace($invalidChar, '', $item->getName()),
                'sku' => $item->getSku(),
                'description' => substr((preg_replace($invalidChar, '', $product->getDescription())),0,150),
                'unit_price' => sprintf('%01.2f', Mage::helper('directory')->currencyConvert($item->getPrice(), $fromCurrency, $toCurrency)),
                'unit_price_usd' => sprintf('%01.2f', $item->getPrice()),
                'quantity' => $item->getQty()
            );
            $commodities[] = $commodity;
        }
        return $commodities;
    }
    
    private function _parseName($name) {
        $session = Mage::getSingleton('checkout/session');
        $quote = $session->getQuote();
        $billing = $quote->getBillingAddress();
        
        $nameComponents = array(
            'firstName' => $billing->getFirstname(),
            'lastName' => $billing->getLastname(),
            'middleInitial' => substr($billing->getMiddleName(), 0, 1)
        );
        
        if ($name == $billing->getName()) {
            return $nameComponents;
        }
        
        if (! $name) {
            return $nameComponents;
        }

        $nameExploded = explode(' ', trim($name));
        if (count($nameComponents) <= 1) {
           return false;
        } elseif (count($nameComponents) >= 2) {
            $nameComponents['firstName'] = $nameExploded[0];
            $lastName = array_values(array_slice($nameExploded, -1, 1, true));
            $nameComponents['lastName'] = $lastName[0];
            if (count($nameExploded) > 2) {
                $nameComponents['middleInitial'] = substr($nameExploded[1], 0, 1);
            } else {
                $nameComponents['middleInitial'] = '';
            }
        }
        return $nameComponents;
    }
    
    public function apiMerchantOrder($simulate = false) {
/*
	if(Mage::app()->getStore()->isAdmin()) {
		Mage::log("backend");
        	Mage::getSingleton('adminhtml/session_quote', array('name' => 'backend'));
	} else {
		Mage::log("frontend");
        	Mage::getSingleton('core/session', array('name' => 'frontend'));
	}
*/

        Mage::getSingleton('core/session', array('name' => 'frontend'));
        Mage::log('STARTING /merchant/order');
        $borderpay = Mage::getModel('borderpay/method_borderpay');
        $apiClient = Mage::getModel('apiclient/apiclient', $this->getConfigData('api_url'));
        $apiClient->setKey($borderpay->getConfigData('api_key'));
        $apiClient->setSecret($borderpay->getConfigData('api_secret'));
        
        $session = Mage::getSingleton('checkout/session');
        $sessionPaymentInfo = $session->getBorderpayPaymentInfo();
        $quote = $session->getQuote();
        $payment = $quote->getPayment();
        $bpPayment = $session->getBorderpayPayment();
        
        $billing = $quote->getBillingAddress();
        $shipping = $quote->getShippingAddress();
        $totals = $quote->getTotals();
        $post = Mage::app()->getRequest()->getPost();
        
        $orderNumber = $bpPayment->getOrderNumber();
        $session->setBorderpayOrderNumber($orderNumber);
        
        if (isset($post['fingerprint']) && $post['fingerprint']) {
            $fingerprint = $post['fingerprint'];
        } elseif (isset($sessionPaymentInfo['fingerprint'])) {
            $fingerprint = $sessionPaymentInfo['fingerprint'];
        } else {
            $fingerprint = '';
        }
        
        if (isset($post['payment']) && $post['payment']) {
            $paymentData = $post['payment'];
        } elseif (isset($sessionPaymentInfo['payment'])) {
            $paymentData = $sessionPaymentInfo['payment'];
        } else {
            $paymentData = array(
                'transaction_type' => '',
                'service' => '',
                'cc_type' => '',
                'cc_number' => '',
                'cc_exp_month' => '',
                'cc_exp_year' => '',
                'cc_cid' => ''
            );
        }

        if (isset($post['payment']['cc_name'])) {
            $nameComponents = $this->_parseName($post['payment']['cc_name']);
        } else {
            $nameComponents = $this->_parseName(null);
        }
        

        $query = array(
            'order_number' => $session->getBordershipBjlOrderNumber(),
            'guid' => $session->getBordershipBjlOrderNumber(),
            'ip_address' => $this->_getIpAddress(),
            
            'shipping_amount' => sprintf('%01.2f', $totals['shipping']->getAddress()->getShippingAmount()),
            'shipping_amount_usd' => sprintf('%01.2f', $totals['shipping']->getAddress()->getBaseShippingAmount()),
            'tax_amount' => sprintf('%01.2f', $totals['grand_total']->getAddress()->getTaxAmount()),
            'tax_amount_usd' => sprintf('%01.2f', $totals['grand_total']->getAddress()->getBaseTaxAmount()),
            'subtotal' => sprintf('%01.2f', $totals['subtotal']->getAddress()->getSubtotal()),
            'subtotal_usd' => sprintf('%01.2f', $totals['subtotal']->getAddress()->getBaseSubtotal()),
            'currency_num' => $this->getIso4217CurrencyCode(Mage::app()->getStore()->getCurrentCurrencyCode()),
            
            'email_address' => $quote->getCustomer()->getEmail(),
            
            'billing' => array(
                'first_name' => $nameComponents['firstName'],
                'last_name' => $nameComponents['lastName'],
                'middle_initial' => $nameComponents['middleInitial'],
                'address' => $billing->getStreet1(),
                'address2' => $billing->getStreet2(),
                'city' => $billing->getCity(),
                'province' => $billing->getRegionCode(),
                'postal_code' => $billing->getPostcode(),
                'country_code' => $billing->getCountryId(),
                'phone' => $billing->getTelephone()
            ),
            
            'shipping' => array(
                'first_name' => $shipping->getFirstname(),
                'last_name' => $shipping->getLastname(),
                'middle_initial' => $shipping->getMiddleName() ? substr($address->getMiddleName(), 0, 1) : '',
                'address' => $shipping->getStreet1(),
                'address2' => $shipping->getStreet2(),
                'city' => $shipping->getCity(),
                'province' => $shipping->getRegionCode(),
                'postal_code' => $shipping->getPostcode(),
                'country_code' => $shipping->getCountryId(),
                'phone' => $shipping->getTelephone()
            ),
            
            'commodities' => $this->_getBorderpayCommodities($quote),
            'fingerprint' => $fingerprint,
            'transaction_type' => $paymentData['transaction_type'],
            'payment_service_id' => isset($paymentData['service']) ? $paymentData['service'] : '',
            'cc_type' => isset($paymentData['cc_type']) ? $paymentData['cc_type'] : '',
            'cc_number' => isset($paymentData['cc_number']) ? $paymentData['cc_number'] : '',
            'expiration_month' => isset($paymentData['cc_exp_month']) ? sprintf('%02s', $paymentData['cc_exp_month']) : '',
            'expiration_year' => isset($paymentData['cc_exp_year']) ? $paymentData['cc_exp_year'] : '',
            'cc_verification' => isset($paymentData['cc_cid']) ? $paymentData['cc_cid'] : ''
        );
               
        Mage::log($query);
        $response = $apiClient->call('/merchant/order/', 'POST', $query);

	// Get the API Response Code and append it to the response body
	$apiStatus = array('apistatus' => $apiClient->getLastResponse()->getStatus());
	$finalResponse = array_merge((array)$response, (array)$apiStatus);

        Mage::log('ENDING /merchant/order');
        return $finalResponse;
	
    }

    protected function getApiClient() {
        if (! $this->apiClient) {
            $this->apiClient = Mage::getModel('apiclient/apiclient', $this->getConfigData('api_url'));
            $this->apiClient->setKey($this->getConfigData('api_key'));
            $this->apiClient->setSecret($this->getConfigData('api_secret'));
        }
        return $this->apiClient;
    }
    
    function getIso4217CurrencyCode($currencyCode) {
        $a = array();
        $a['AFA'] = array('Afghan Afghani', '971');
        $a['AWG'] = array('Aruban Florin', '533');
        $a['AUD'] = array('Australian Dollars', '036');
        $a['ARS'] = array('Argentine Peso', '032');
        $a['AZN'] = array('Azerbaijanian Manat', '944');
        $a['BSD'] = array('Bahamian Dollar', '044');
        $a['BDT'] = array('Bangladeshi Taka', '050');
        $a['BBD'] = array('Barbados Dollar', '052');
        $a['BYR'] = array('Belarussian Rouble', '974');
        $a['BOB'] = array('Bolivian Boliviano', '068');
        $a['BRL'] = array('Brazilian Real', '986');
        $a['GBP'] = array('British Pounds Sterling', '826');
        $a['BGN'] = array('Bulgarian Lev', '975');
        $a['KHR'] = array('Cambodia Riel', '116');
        $a['CAD'] = array('Canadian Dollars', '124');
        $a['KYD'] = array('Cayman Islands Dollar', '136');
        $a['CLP'] = array('Chilean Peso', '152');
        $a['CNY'] = array('Chinese Renminbi Yuan', '156');
        $a['COP'] = array('Colombian Peso', '170');
        $a['CRC'] = array('Costa Rican Colon', '188');
        $a['HRK'] = array('Croatia Kuna', '191');
        $a['CPY'] = array('Cypriot Pounds', '196');
        $a['CZK'] = array('Czech Koruna', '203');
        $a['DKK'] = array('Danish Krone', '208');
        $a['DOP'] = array('Dominican Republic Peso', '214');
        $a['XCD'] = array('East Caribbean Dollar', '951');
        $a['EGP'] = array('Egyptian Pound', '818');
        $a['ERN'] = array('Eritrean Nakfa', '232');
        $a['EEK'] = array('Estonia Kroon', '233');
        $a['EUR'] = array('Euro', '978');
        $a['GEL'] = array('Georgian Lari', '981');
        $a['GHC'] = array('Ghana Cedi', '288');
        $a['GIP'] = array('Gibraltar Pound', '292');
        $a['GTQ'] = array('Guatemala Quetzal', '320');
        $a['HNL'] = array('Honduras Lempira', '340');
        $a['HKD'] = array('Hong Kong Dollars', '344');
        $a['HUF'] = array('Hungary Forint', '348');
        $a['ISK'] = array('Icelandic Krona', '352');
        $a['INR'] = array('Indian Rupee', '356');
        $a['IDR'] = array('Indonesia Rupiah', '360');
        $a['ILS'] = array('Israel Shekel', '376');
        $a['JMD'] = array('Jamaican Dollar', '388');
        $a['JPY'] = array('Japanese yen', '392');
        $a['KZT'] = array('Kazakhstan Tenge', '368');
        $a['KES'] = array('Kenyan Shilling', '404');
        $a['KWD'] = array('Kuwaiti Dinar', '414');
        $a['LVL'] = array('Latvia Lat', '428');
        $a['LBP'] = array('Lebanese Pound', '422');
        $a['LTL'] = array('Lithuania Litas', '440');
        $a['KWD'] = array('Kuwaiti Dinar', '414');
        $a['LVL'] = array('Latvia Lat', '428');
        $a['LBP'] = array('Lebanese Pound', '422');
        $a['LTL'] = array('Lithuania Litas', '440');
        $a['MOP'] = array('Macau Pataca', '446');
        $a['MKD'] = array('Macedonian Denar', '807');
        $a['MGA'] = array('Malagascy Ariary', '969');
        $a['MYR'] = array('Malaysian Ringgit', '458');
        $a['MTL'] = array('Maltese Lira', '470');
        $a['BAM'] = array('Marka', '977');
        $a['MUR'] = array('Mauritius Rupee', '480');
        $a['MXN'] = array('Mexican Pesos', '484');
        $a['MZM'] = array('Mozambique Metical', '508');
        $a['NPR'] = array('Nepalese Rupee', '524');
        $a['ANG'] = array('Netherlands Antilles Guilder', '532');
        $a['TWD'] = array('New Taiwanese Dollars', '901');
        $a['NZD'] = array('New Zealand Dollars', '554');
        $a['NIO'] = array('Nicaragua Cordoba', '558');
        $a['NGN'] = array('Nigeria Naira', '566');
        $a['KPW'] = array('North Korean Won', '408');
        $a['NOK'] = array('Norwegian Krone', '578');
        $a['OMR'] = array('Omani Riyal', '512');
        $a['PKR'] = array('Pakistani Rupee', '586');
        $a['PYG'] = array('Paraguay Guarani', '600');
        $a['PEN'] = array('Peru New Sol', '604');
        $a['PHP'] = array('Philippine Pesos', '608');
        $a['QAR'] = array('Qatari Riyal', '634');
        $a['RON'] = array('Romanian New Leu', '946');
        $a['RUB'] = array('Russian Federation Ruble', '643');
        $a['SAR'] = array('Saudi Riyal', '682');
        $a['CSD'] = array('Serbian Dinar', '891');
        $a['SCR'] = array('Seychelles Rupee', '690');
        $a['SGD'] = array('Singapore Dollars', '702');
        $a['SKK'] = array('Slovak Koruna', '703');
        $a['SIT'] = array('Slovenia Tolar', '705');
        $a['ZAR'] = array('South African Rand', '710');
        $a['KRW'] = array('South Korean Won', '410');
        $a['LKR'] = array('Sri Lankan Rupee', '144');
        $a['SRD'] = array('Surinam Dollar', '968');
        $a['SEK'] = array('Swedish Krona', '752');
        $a['CHF'] = array('Swiss Francs', '756');
        $a['TZS'] = array('Tanzanian Shilling', '834');
        $a['THB'] = array('Thai Baht', '764');
        $a['TTD'] = array('Trinidad and Tobago Dollar', '780');
        $a['TRY'] = array('Turkish New Lira', '949');
        $a['AED'] = array('UAE Dirham', '784');
        $a['USD'] = array('US Dollars', '840');
        $a['UGX'] = array('Ugandian Shilling', '800');
        $a['UAH'] = array('Ukraine Hryvna', '980');
        $a['UYU'] = array('Uruguayan Peso', '858');
        $a['UZS'] = array('Uzbekistani Som', '860');
        $a['VEB'] = array('Venezuela Bolivar', '862');
        $a['VND'] = array('Vietnam Dong', '704');
        $a['AMK'] = array('Zambian Kwacha', '894');
        $a['ZWD'] = array('Zimbabwe Dollar', '716');
        if ($a[$currencyCode]) {
            return $a[$currencyCode][1];
        } else {
            return false;
        }
    }
}
