<?

class BorderJump_BorderPay_Model_Observer {
    protected $borderpay = null;

    public function __construct() {}

    
    private $_preDispatchHasRun = false;
    public function controller_action_predispatch_checkout_onepage_saveOrder_hook($observer) {
        Mage::getSingleton('core/session', array('name' => 'frontend'));

        $session = Mage::getSingleton('checkout/session');
        foreach($session->getQuote()->getAllItems() as $item) {
            $product = $item->getProduct();
            $product->load($product->getEntityId());
        }
        if ($this->_preDispatchHasRun) {
            $this->_preDispatchHasRun = false;
            return $observer;
        }
 
        $result = null;
        if ($session->getBorderpayIsThirdPartyDone()) {
            $session->setBorderpayIsThirdPartyDone();
            return $observer;
        }
        
        $payment = $session->getQuote()->getPayment();
        $code = $payment->getMethodInstance()->getCode();
        if (strtolower($code) != 'borderpay') {
            return $observer;
        }
        
        $paymentInfo = $session->getBorderpayPaymentInfo();
        if ($paymentInfo['payment']['service'] != 6) {
            return $observer;
        }
        
        $request = Mage::app()->getRequest();
        $action = $request->getActionName();

        $borderpay = Mage::getModel('borderpay/method_borderpay');
        $response = $borderpay->apiMerchantOrder();

        $api_status = Mage::helper('borderpay/response')->getApiStatus($response);
        $status = Mage::helper('borderpay/response')->getStatus($response);
        $isEnrolled = Mage::helper('borderpay/response')->isEnrolled($response);
        $error = null;
              
	// Check the HTTP response code from the api call. Throw an error on anything != 200
        if ($api_status != '200') {
	    Mage::log("Error: API returned ".$api_status);

	    $error = 'There was an error placing your order. Please try again later.';
            Mage::app()->getFrontController()->getAction()->setFlag($action, Mage_Core_Controller_Varien_Action::FLAG_NO_DISPATCH, true);
            $result = array(
                'success' => false,
                'error' => 'true',
                'error_messages' => 'There was an error placing your order. Please try again later.'
            );
        } 

        if ($status == 'declined') {
            $error = 'There was an error placing your order. Please try again later';
        }
        
        if (! $error) {
            if ($isEnrolled) {
                Mage::log('enrolled; saving session stuff');
                $session->setBorderpayTransactionData(array(
                    'PaReq' => $response['payload'],
                    'ACSUrl' => $response['ACSUrl'],
                    'MD' => $response['transaction_id']
                ));
                $session->setBorderpayPayload($response['payload']);
                $session->setBorderpayTransactionId($response['transaction_id']);
                $session->setBorderpayTransactionType('CC');
                $session->setBorderpayReturnUrl(Mage::getUrl());
                
                Mage::app()->getFrontController()->getAction()->setFlag($action, Mage_Core_Controller_Varien_Action::FLAG_NO_DISPATCH, true);
                $result = array(
                    'success' => false,
                    'error' => false,
                    'redirect' => Mage::getUrl("checkout/onepage/thirdPartySubmit"),
                );
            } else {
                $session->setBorderpayOrderStatus($status);
                $this->_preDispatchHasRun = true;
                return $observer;
            }
        }

        if (! $result) {
            Mage::app()->getFrontController()->getAction()->setFlag($action, Mage_Core_Controller_Varien_Action::FLAG_NO_DISPATCH, true);
            $result = array(
                'success' => false,
                'error' => 'true',
                'error_messages' => 'There was an error placing your order. Please try again later'
            );
        }

        $response = Mage::app()->getResponse();
        $response->setHttpResponseCode(200);
        $json = Mage::helper('core')->jsonEncode($result);
        $response->setBody($json);
        $this->_preDispatchHasRun = true;
        return $observer;
    }
    
    public function sales_order_place_before_hook($observer) {
        Mage::getSingleton('core/session', array('name' => 'frontend'));
        $session = Mage::getSingleton('checkout/session');
        $status = $session->getBorderpayOrderStatus();
        $session->setBorderpayOrderStatus();
        
        $order = $observer->getOrder();
        if ($status == 'pending') {
            $order->setState(Mage_Sales_Model_Order::STATE_NEW, true);
        } elseif ($status == 'approved') {
            $order->setState(Mage_Sales_Model_Order::STATE_PROCESSING, true);
        }
        
        return $observer;
    }
    
    public function sales_order_place_after_hook($observer) {
        $session = Mage::getSingleton('checkout/session');
        $order = $observer->getEvent()->getOrder();
        $payment = $order->getPayment();
        $code = $payment->getMethodInstance()->getCode();
        if (strtolower($code) != 'borderpay') {
            return $observer;
        }
        
        $bpPayment = $session->getBorderpayPayment();
        if ($bpPayment) {
            $bpPayment->setMagePayment($payment);
            $bpPayment->save();
        }

        return $observer;
    }
}

?>
