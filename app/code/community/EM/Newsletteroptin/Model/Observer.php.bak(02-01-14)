<?php
class EM_Newsletteroptin_Model_Observer
{
    public function setCustomerIsSubscribed($observer)
    {
        if ((bool) Mage::getSingleton('checkout/session')->getCustomerIsSubscribed()){
            $quote = $observer->getEvent()->getQuote();
            $customer = $quote->getCustomer();
            switch ($quote->getCheckoutMethod()){
                case Mage_Sales_Model_Quote::CHECKOUT_METHOD_REGISTER:
                    $customer->setIsSubscribed(1);
                    break;
				case Mage_Sales_Model_Quote::CHECKOUT_METHOD_LOGIN_IN:
					$customer->setIsSubscribed(1);
					break;
                case Mage_Sales_Model_Quote::CHECKOUT_METHOD_GUEST:
                    $session = Mage::getSingleton('core/session');
                    try {
						$email=$quote->getBillingAddress()->getEmail();
						$subscriber = Mage::getModel('newsletter/subscriber')->loadByEmail($email);
						$subscriber->setSubscriberEmail('test');
						$subscriber->save();
                        $status = Mage::getModel('newsletter/subscriber')->subscribe($quote->getBillingAddress()->getEmail());
                        if ($status == Mage_Newsletter_Model_Subscriber::STATUS_NOT_ACTIVE){
                            $session->addSuccess(Mage::helper('newsletteroptin')->__('Confirmation request has been sent regarding your newsletter subscription'));
                        }
                    }
                    catch (Mage_Core_Exception $e) {
                        $session->addException($e, Mage::helper('newsletteroptin')->__('There was a problem with the newsletter subscription: %s', $e->getMessage()));
                    }
                    catch (Exception $e) {
                        $session->addException($e, Mage::helper('newsletteroptin')->__('There was a problem with the newsletter subscription'));
                    }
                    break;
            }
            //Mage::getSingleton('checkout/session')->setCustomerIsSubscribed(0);
        }else{
		   
		   $quote = $observer->getEvent()->getQuote();
            $customer = $quote->getCustomer();
          
             
                    $session = Mage::getSingleton('core/session');
                    try {
						Mage::getSingleton('checkout/session')->setCustomerIsSubscribed(0);
                        //$status = Mage::getModel('newsletter/subscriber')->subscribe($quote->getBillingAddress()->getEmail());
						$email=$quote->getBillingAddress()->getEmail();
						
							//$subscriber = Mage::getModel('newsletter/subscriber')->subscribe($email);
							$customer = Mage::getModel('customer/customer')->setWebsiteId(1)->loadByEmail($email);
							
							$subscriber = Mage::getModel('newsletter/subscriber')->loadByEmail($email);
							$subscriber->setStatus(Mage_Newsletter_Model_Subscriber::STATUS_UNSUBSCRIBED);
							$subscriber->setSubscriberEmail($email);
							$subscriber->setSubscriberConfirmCode($subscriber->RandomSequence());
							$subscriber->setStoreId(Mage::app()->getStore()->getId());
							$subscriber->setCustomerId($customer->getId());
							try {
							$subscriber->save();
							$subscriber = Mage::getModel('newsletter/subscriber')->subscribe($email);
							}
							catch (Exception $e) {
							throw new Exception($e->getMessage());
							}
						
						

						/*if ($customer->getId()){
							$subscriber = Mage::getModel('newsletter/subscriber')->loadByEmail($email);
							if (!$subscriber->getId()|| $subscriber->getStatus() == Mage_Newsletter_Model_Subscriber::STATUS_UNSUBSCRIBED||subscriber->getStatus() == Mage_Newsletter_Model_Subscriber::STATUS_NOT_ACTIVE) {
							$subscriber->setStatus(Mage_Newsletter_Model_Subscriber::STATUS_SUBSCRIBED);
							$subscriber->setSubscriberEmail($email);
							$subscriber->setSubscriberConfirmCode($subscriber->RandomSequence());
							}
							$subscriber->setStoreId(Mage::app()->getStore()->getId());
							$subscriber->setCustomerId($customer->getId());
							try {
							$subscriber->save();
							}
							catch (Exception $e) {
							throw new Exception($e->getMessage());
							}
						}*/
						
						
						
						
						
                       
                    }
                    catch (Mage_Core_Exception $e) {
                        $session->addException($e, Mage::helper('newsletteroptin')->__('There was a problem with the newsletter subscription: %s', $e->getMessage()));
                    }
                   
                   
           
            //Mage::getSingleton('checkout/session')->setCustomerIsSubscribed(0);
	}
	}
}