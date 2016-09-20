<?php

include_once('Mage/Checkout/controllers/OnepageController.php');
class BorderJump_BorderPay_OnepageController extends Mage_Checkout_OnepageController {
    
    protected function _rewrite() {
        //call the parent rewrite method so every that needs
        //to happen happens
        $original_result = parent::_rewrite();

        //fire the event ourselve, since magento isn't firing it
        Mage::dispatchEvent(
            'controller_action_predispatch_'.$this->getFullActionName(),
            array('controller_action'=>$this)
        );      
        
        //return the original result in case another method is relying on it
        return $original_result;
    }
    
    public function cronTestAction() {
        $params = Mage::app()->getRequest()->getParams();
        $borderpay = Mage::getSingleton('borderpay/method_borderpay');
        $borderpay->updateAllPendingOrders($params['real']);
    }
    
    public function saveAdditionalDataAction() {
        Mage::getSingleton('core/session', array('name' => 'frontend'));
        $session = Mage::getSingleton('checkout/session');
        $post = Mage::app()->getRequest()->getPost();
        $payment = $session->getQuote()->getPayment();
        
        $orderNumber = Mage::getModel('borderpay/method_borderpay')->createOrderNumber();
        $sessionData = array(
            'fingerprint' => $post['fingerprint'],
            'order_number' => $orderNumber,
            'payment' => $post['payment']
        );
        
        $session->setBorderpayPaymentInfo($sessionData);
        $bpPayment = Mage::getModel('borderpay/payment');
        $bpPayment->setOrderNumber($orderNumber);
        
        $session->setBorderpayPayment($bpPayment);
        
        echo json_encode(true);
    }
    
    // creates an html form and submits it to a third party
    public function thirdPartySubmitAction() {
        Mage::getSingleton('core/session', array('name' => 'frontend'));
        $session = Mage::getSingleton('checkout/session');
        $data = $session->getBorderpayTransactionData();
        $action = $data['ACSUrl'];
        echo "<html><body>";
        echo "<form id='submitMe' name='submitMe' method='post' action='$action 'style='visibility: hidden'>";
        foreach ($data as $k => $v) {
            echo "<input name='$k' value='$v' />";
        }
        echo '<input name="TermUrl" value="' . Mage::getUrl("checkout/onepage/authorizePayment") . '">';
        echo '<input type="submit" />';
        echo '</form>';
        echo "
            <script>
                window.onload = function() {
                    document.submitMe.submit();
                };
            </script>
        ";
        echo "</body></html>";
        $session->setBorderpayIsThirdPartyDone(true);
        $session->setBorderpayTransactionData();
    }
    
    public function submitPaymentAction() {
        Mage::getSingleton('core/session', array('name' => 'frontend'));
        $post = Mage::app()->getRequest()->getPost();
        $borderpay = Mage::getSingleton('borderpay/method_borderpay');
        $response = $borderpay->apiMerchantOrder();
        $session = Mage::getSingleton('checkout/session');
        
        if (isset($response['transaction_id']) && $response['transaction_id']) {
            $session->setBorderpayTransactionId($response['transaction_id']);
        }
        if (isset($post['payment']['transaction_type']) && $post['payment']['transaction_type']) { 
            $session->setBorderpayTransactionType($post['payment']['transaction_type']);
        }
        echo json_encode($response);
    }
    
    public function authorizePaymentAction() {
        Mage::getSingleton('core/session', array('name' => 'frontend'));
        $session = Mage::getSingleton('checkout/session');
        $borderpay = Mage::getSingleton('borderpay/method_borderpay');
        $response = $borderpay->apiMerchantOrderAuth();
        if ($response) {
            $status = Mage::helper('borderpay/response')->getStatus($response);
            
            // approved
            if ($status === 'approved' || $status === 'pending') {
                $checkout = Mage::getSingleton('checkout/type_onepage');
                
                // save custom session addresses if they exist. magento
                // is flaky about saving them to its session properly.
                if ($session->getBorderpayShippingAddress()) {
                    $checkout->saveShipping($session->getBorderpayShippingAddress());
                }
                if ($session->getBorderpayBillingAddress()) {
                    $checkout->saveBilling($session->getBorderpayBillingAddress());
                }
                
                $checkout->saveShippingMethod($session->getQuote()->getShippingAddress()->getShippingMethod());
                
                $bjlNumber = $session->getBordershipBjlOrderNumber();
                $checkout->savePayment(array('method' => 'borderpay'));
                $checkout->saveOrder();
                $session->setBordershipBjlOrderNumber($bjlNumber);
                
                $session->setBorderpayTransactionId();
                $session->setBorderpayTransactionType();
                $session->setBorderpayPaymentInfo();
                
                foreach ($session->getQuote()->getItemsCollection() as $item) {
                    Mage::getSingleton('checkout/cart')->removeItem($item->getId())->save();
                }
                
                $this->_redirect('checkout/onepage/success', array('_secure' => true));
            
            // declined
            } elseif ($status === 'declined') {
                $this->loadLayout();
                $block = $this->getLayout()->createBlock(
                    'Mage_Core_Block_Template',
                    'borderpay.error',
                    array('template' => 'borderpay/checkout/onepage/error.phtml')
                );
                $this->getLayout()->getBlock('root')->setTemplate('page/1column.phtml');
                $this->getLayout()->getBlock('content')->append($block);
                $this->renderLayout();
                
            // customer cancelled transaction
            } elseif ($status === 'cancelled') {
                $this->_redirect('checkout/onepage');
            }
        } else {
            $this->_redirect('checkout/onepage');
        }
        
        // clear out the session variables in any case
        $session->setBorderpayTransactionId();
    }
    
    public function apiMerchantOrderAction() {
        $borderpay = Mage::getSingleton('borderpay/method_borderpay');
        $response = $borderpay->apiMerchantOrder();
        echo json_encode($response);
    }
    
    public function apiMerchantOrderAuthAction() {
        $borderpay = Mage::getSingleton('borderpay/method_borderpay');
        $response = $borderpay->apiMerchantOrderAuth();
        echo json_encode($response);
    }
    
    public function saveAddressesAction() {
        $session = Mage::getSingleton('checkout/session');
        $billing = $session->getQuote()->getBillingAddress()->getData();
        $shipping = $session->getQuote()->getShippingAddress()->getData();
        
        $billing['address'] = $billing['street'];
        $shipping['address'] = $shipping['street'];
        $session->setBorderpayBillingAddress($billing);
        $session->setBorderpayShippingAddress($shipping);
    }
}