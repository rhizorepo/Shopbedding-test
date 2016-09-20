<?php
/*
 * Created on Apr 2, 2009
 *
 */

class Ebizmarts_Mailchimp_Model_Newsletter_Subscriber extends Mage_Newsletter_Model_Subscriber
{
    public function subscribe($email, $name = array('first' => 'Guest', 'last' => 'Guest'))
    {
        $this->loadByEmail($email);
        $customer = Mage::getModel('customer/customer')
           ->setWebsiteId(Mage::app()->getStore()->getWebsiteId())
           ->loadByEmail($email);
        $isNewSubscriber = false;

        $customerSession = Mage::getSingleton('customer/session');

        if(!$this->getId()) {
            $this->setSubscriberConfirmCode($this->randomSequence());
        }

        if (!$this->getId() || $this->getStatus()==self::STATUS_UNSUBSCRIBED || $this->getStatus()==self::STATUS_NOT_ACTIVE) {
            if (Mage::getStoreConfig(self::XML_PATH_CONFIRMATION_FLAG) == 1) {
                $this->setStatus(self::STATUS_NOT_ACTIVE);
            } else {
                $this->setStatus(self::STATUS_SUBSCRIBED);
            }
            $this->setSubscriberEmail($email);
        }
		$name['SOURCE'] = '';
		if(Mage::app()->getRequest()->getRouteName() == 'checkout'){
			$name['SOURCE'] = 'checkout';
		}
		if(Mage::app()->getRequest()->getRouteName() == 'paypal'){
			$name['SOURCE'] = 'paypal';
		}

        if ($customerSession->isLoggedIn()) {
            $this->setStoreId($customerSession->getCustomer()->getStoreId());
            $this->setStatus(self::STATUS_SUBSCRIBED);
            $this->setCustomerId($customerSession->getCustomerId());
        } else if ($customer->getId()) {
            $this->setStoreId($customer->getStoreId());
            $this->setSubscriberStatus(self::STATUS_SUBSCRIBED);
            $this->setCustomerId($customer->getId());
        } else {
            $this->setStoreId(Mage::app()->getStore()->getId());

			$quote = Mage::getSingleton('checkout/session')->getQuote();
            $billingAddress = $quote->getBillingAddress();
             $firstName = $billingAddress->getFirstname();
			 $lastName = $billingAddress->getLastname();
            $this->setSubscriberFirstName($firstName);
            $this->setSubscriberLastName($lastName);
			$this->setSubscriberSource($name['SOURCE']);
            $this->setCustomerId(0);
            $isNewSubscriber = true;
        }

		$this->setSubscriberSubscribedAt(date('Y-m-d'));
        $this->setIsStatusChanged(true);

        try {
            $this->save();
            if (Mage::getStoreConfig(self::XML_PATH_CONFIRMATION_FLAG) == 1
               && $this->getSubscriberStatus()==self::STATUS_NOT_ACTIVE) {
                   $this->sendConfirmationRequestEmail();
            } else {
                //$this->sendConfirmationSuccessEmail();
            }
			/// SET SOURCE
			//$name['SOURCE'] = Mage::app()->getRequest()->getRouteName();

			#ebizmarts
	        Mage::getSingleton('mailchimp/mailchimp')->subscribe($email, $name);

            return $this->getStatus();
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function unsubscribe()
    {

        if ($this->hasCheckCode() && $this->getCode() != $this->getCheckCode()) {
            Mage::throwException(Mage::helper('newsletter')->__('Invalid subscription confirmation code'));
        }

        $this->setSubscriberStatus(self::STATUS_UNSUBSCRIBED)
            ->save();
        $this->sendUnsubscriptionEmail();

        #ebizmarts
        Mage::getModel('mailchimp/mailchimp')->unsubscribe($this->getEmail());

        return $this;
    }

    /**
     * Saving customer cubscription status
     *
     * @param   Mage_Customer_Model_Customer $customer
     * @return  Mage_Newsletter_Model_Subscriber
     */
    public function subscribeCustomer($customer)
    {
        $this->loadByCustomer($customer);

        if ($customer->getImportMode()) {
            $this->setImportMode(true);
        }

        if (!$customer->getIsSubscribed() && !$this->getId()) {
            // If subscription flag not seted or customer not subscriber
            // and no subscribe bellow
            return $this;
        }

        if(!$this->getId()) {
            $this->setSubscriberConfirmCode($this->randomSequence());
        }

        if($customer->hasIsSubscribed()) {
            $status = $customer->getIsSubscribed() ? self::STATUS_SUBSCRIBED : self::STATUS_UNSUBSCRIBED;
        } else {
            $status = ($this->getStatus() == self::STATUS_NOT_ACTIVE ? self::STATUS_UNSUBSCRIBED : $this->getStatus());
        }


        if($status != $this->getStatus()) {
            $this->setIsStatusChanged(true);
        }

        $this->setStatus($status);

        if(!$this->getId()) {
            $this->setStoreId($customer->getStoreId())
                ->setCustomerId($customer->getId())
                ->setEmail($customer->getEmail());
            $this->setSubscriberSubscribedAt(date('Y-m-d'));
        } else {
            $this->setEmail($customer->getEmail());
        }

        $this->save();

        #ebizmarts
//        print_r($this->getStatus());
//        exit;

        if ((!$customer->isConfirmationRequired()) && !$customer->getConfirmation()) {
	        if ( $this->getStatus() == self::STATUS_SUBSCRIBED ) {
	        	Mage::getSingleton('mailchimp/mailchimp')->subscribe($customer);

	        } else {
	        	Mage::getSingleton('mailchimp/mailchimp')->unsubscribe($customer);
	        }
        }

        $sendSubscription = $customer->getData('sendSubscription');
        if (is_null($sendSubscription) xor $sendSubscription) {
            if ($this->getIsStatusChanged() && $status == self::STATUS_UNSUBSCRIBED) {
                $this->sendUnsubscriptionEmail();
            } elseif ($this->getIsStatusChanged() && $status == self::STATUS_SUBSCRIBED) {
                $this->sendConfirmationSuccessEmail();
            }
        }
        return $this;
    }

}

?>
