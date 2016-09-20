<?php
class BorderJump_BorderShip_Model_Order_Shipment extends Mage_Sales_Model_Order_Shipment
{
    protected function _shouldFilterTracks() {
        if ($this->getOrder()->getShippingCarrier()->getCarrierCode() != 'bordership') {
            return false;
        }
        
        $isAdmin = Mage::getSingleton('adminhtml/session')->getLocale();
        $action = Mage::app()->getRequest()->getActionName();
        if (! $isAdmin || $action == 'email' || $action == 'save') {
            return true;
        }
        return false;
        
    }
    
    public function getAllTracks($test = null) {
        $tracks = array();
        foreach ($this->_getRealTracksCollection() as $track) {
            if (!$track->isDeleted()) {
                $tracks[] =  $track;
            }
        }
        
        if (! $this->_shouldFilterTracks()) {
            return $tracks;
        }
        
        $coolTracks = array();
        foreach($tracks as $t) {
            if ($t->getCarrierCode() == 'bordership') {
                $coolTracks[] = $t;
            }
        }
        return $coolTracks;
    }
    
    protected function _getRealTracksCollection() {
        return parent::getTracksCollection();
    }
    
    public function getTracksCollection() {
        $tracks = parent::getTracksCollection();
        if (! $this->_shouldFilterTracks()) {
            return $tracks;
        }
        
        return $tracks->clear()->addFieldToFilter('carrier_code', 'bordership');
    }
}