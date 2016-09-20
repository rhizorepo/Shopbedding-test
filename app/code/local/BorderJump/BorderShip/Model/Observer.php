<?php

class BorderJump_BorderShip_Model_Observer {
    protected $bordership = null;
    
    public function __construct() {}
    
    protected function _getBordership() {
        if (! isset($this->bordership)) {
            $this->bordership = Mage::getModel('bordership/carrier_bordership');
        }
        return $this->bordership;
    }
    
    public function sales_order_place_before_hook($observer) {
        return $observer;
    }
    
    /**
     * This MUST NOT be called until the order payment has been approved
     * by borderpay.
     */
    public function sales_order_place_after_hook($observer) {
        // get the order
        $order = $observer->getOrder();

        // get the carrier
        $carrier = $order->getShippingCarrier();
        
        if ($carrier->getCarrierCode() === 'bordership') {
            $this->_modifyShippingDescription($order)->save();
            $this->_saveBordershipOrder($order);

            // set Magento status
            //$order->setStatus('pending_borderjump');
            //$order->save();
        }
        
        return $observer;
    }
    
    public function sales_order_shipment_save_before_hook($observer) {
        return $observer;
    }
    
    public function sales_order_shipment_save_after_hook($observer) {
        return $observer;
    }
    
    public function sales_order_shipment_track_save_before_hook($observer) {
        return $observer;
    }
    
    public function sales_order_shipment_track_save_after_hook($observer) {
        Mage::getSingleton('core/session', array('name' => 'adminhtml'));
        
        // get the track object
        $track = $observer->getTrack();
        
        // return if the track is being updated rather than created.
        if($track->getData('created_at') != $track->getData('updated_at')) {
            return;
        }
        
        // return if it's not a bordership order
        $carrier = $track->getShipment()->getOrder()->getShippingCarrier()->getCarrierCode();
        if ($carrier != 'bordership') {
            return $observer;
        }

        // check current Magento status to see if we should attempt inbound parcel
        $order = $track->getShipment()->getOrder();
        if($order->getStatus() == 'pending_borderjump') {
            Mage::getSingleton('adminhtml/session')->getMessages(true);
            Mage::throwException('Orders with a status of "Pending BorderJump" are not ready to ship.'); 
            return $observer;
        }

        // return if this is the BJL generated tracking number
        if ($track->getCarrierCode() == 'bordership') {
            return $observer;
        }
        
        // check if we can add more tracks
        if (! $this->_canAddTrack($track)) {
            Mage::getSingleton('adminhtml/session')->getMessages(true);
            Mage::throwException('You cannot add that many tracking numbers for this shipment.'); 
            return $observer;
        }
        
        $responseBody = $this->_getBordership()->apiParcel($track);
        $this->_saveBordershipTrackingNumber($track, $responseBody['bjlParcelNumber']);
        
        if ($order->getStatus() == 'processing') {
            $order->setStatus('complete');
            $order->save();
        }
        
        return $observer;
    }
    
    public function sales_order_shipment_track_delete_before_hook($observer) {
        $track = $observer->getTrack();
        
        // get the carrier
        $carrier = $track->getShipment()->getOrder()->getShippingCarrier();
        
        // return if the carrier isn't bordership
        if ($carrier->getCarrierCode() != 'bordership') {
            return $observer;
        }
        
        $bsTrack = Mage::getModel('bordership/track')->loadByMageTrack($track);
        if ($bsTrack) {
            $bjlNumber = $bsTrack->getMageBjlTrack()->getNumber();
        }
        if ($bjlNumber) {
            $this->_getBordership()->apiParcelCancel($bjlNumber);
        }
        return $observer;
    }
    
    protected function _deleteAllTracks($tracks) {
        foreach ($tracks as $track) {
            $track->delete();
        }
    }
    
    // janky hasRun variable to prevent an endless loop where it tries to delete each track,
    // which calls this event hook again, which tries to delete each track, which calls this
    // event hook again, which...
    protected $_hasRun = false;
    public function sales_order_shipment_track_delete_after_hook($observer) {
        $track = $observer->getTrack();
        
        // get the carrier
        $carrier = $track->getShipment()->getOrder()->getShippingCarrier();
        
        // return if the carrier isn't bordership
        if ($carrier->getCarrierCode() != 'bordership') {
            return $observer;
        }
        
        $tracks = $observer->getTrack()->getShipment()->getAllTracks();
        if ($tracks && $this->_hasRun == false) {
            $this->_hasRun = true;
            $this->_deleteAllTracks($tracks);
        }
        
        return $observer;
    }
    
    protected function _modifyShippingDescription($order) {
        $prefix = 'International Shipping by BorderJump';
        $desc = $order->getShippingDescription();
        if (substr($desc, 0, strlen($prefix)) != $prefix) {
            $desc = $prefix . ' - ' . $desc;
            $order->setShippingDescription($desc);
        }
        return $order;
    }
    
    // This is called when the payment is cancelled, which only happens
    // when an order is cancelled. Enterprise apparently doesn't have
    // any event hooks for cancelling orders for some reason.
    public function sales_order_payment_cancel_hook($observer) {
        // get the order
        $order = $observer->getPayment()->getOrder();
        
        // get the carrier
        $carrier = $order->getShippingCarrier();
        
        // return if the carrier isn't bordership
        if ($carrier->getCarrierCode() != 'bordership') {
            return $observer;
        }
        
        // make api call placing the order and save the response
        $response = $carrier->apiOrderCancel($order);
        $status = $carrier->getApiClient()->getLastResponse()->getStatus();
        
        if ($status != '200') {
            Mage::throwException(Mage::helper('bordership')->
                __('There was an error canceling the order with BorderShip.'));
            exit;
        } else {
	    // Change order status to cancelled
	    $order->setStatus('canceled');
	    $order->save();
	}
    }
    
    protected function _saveBordershipOrder($order) {
        $bordership = Mage::getModel('bordership/carrier_bordership');
        try {
            $responseBody = $bordership->apiOrder($order);
            $response = $bordership->getApiClient()->getLastResponse();
            $status = $response->getStatus();
        } catch (Exception $e) {
            Mage::throwException(Mage::helper('bordership')->
                __('Your order could not be completed. Please contact customer support.'));
            Mage::log(Mage::helper('bordership')->
                __('Error creating order with BorderShip: %s', $e));
        }
        
        $orderStatus = strtolower($responseBody['orderConfirmationStatus']);
        $success = ($orderStatus == 'placed' || $orderStatus == 'pending');
        if ($status == '201' && $success) {
            Mage::getSingleton('core/session', array('name' => 'frontend'));
            $session = Mage::getSingleton('checkout/session');
            
            $bordershipOrder = Mage::getModel('bordership/order');
            $bordershipOrder->setOrderNumber($responseBody['bjlOrderNumber']);
            $bordershipOrder->setOrderReference($order->getIncrementId());
            $bordershipOrder->setOrderId($order->getEntityId());
            $bordershipOrder->save();
            
            $session->setBordershipBjlOrderNumber();
        } else {
            Mage::throwException(Mage::helper('bordership')->
                __('Your order could not be completed. Please contact customer support.'));
        }
    }
    
    private function _saveBordershipTrackingNumber($track, $bjlParcelNumber) {
        // get the shipment
        $shipment = $track->getShipment();
        
        // get the carrier.
        $carrier = $shipment->getOrder()->getShippingCarrier();
        
        // stop if it's not a BorderShip order
        $carrierCode = $carrier->getCarrierCode();
        if ($carrierCode != 'bordership') {
            return;
        }
        
        // set the title for the new tracking object.
        $title = 'BorderJump Parcel';
        
        $bjlTrack = Mage::getModel('sales/order_shipment_track');
        $bjlTrack->setNumber($bjlParcelNumber);
        $bjlTrack->setTitle($title);
        $bjlTrack->setCarrierCode($carrierCode);
        $bjlTrack->setShipment($shipment);
        $bjlTrack->setOrderId($shipment->getOrder()->getData('entity_id'));
        $shipment->addTrack($bjlTrack)->save();
        
        $bsTrack = Mage::getModel('bordership/track');
        $bsTrack->setMageBjlTrack($bjlTrack);
        $bsTrack->setMageTrack($track);
        $bsTrack->save();
    }
    
    protected function _canAddTrack($track) {
        $tracks = $track->getShipment()->getAllTracks();
        if (count($tracks) > 1) {
            return false;
        }
        return true;
    }
    
    protected function _getCarrierCode($order) {
        
        // get the shipping method id
        $shippingMethod = $order->getShippingMethod();
        
        // take the 'bordership_' prefix off the method and return it.
        // this is jank.
        return substr($shippingMethod, 11);
    }
    
    protected function _makeParcelId($track) {
        
        // get the shipment id
        $shipmentId = $track->getShipment()->getIncrementId();
        
        // get entity id
        $trackId = $track->getEntityId();
        
        return $shipmentId . $trackId;
    }
    
    protected function _getParcelId($track) {
        return $this->_makeParcelId($track);
    }
}
