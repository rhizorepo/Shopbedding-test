<?php
 
class BorderJump_BorderShip_Model_Track extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('bordership/track');
    }
    
    public function getMageTrack() {
        return Mage::getModel('sales/order_shipment_track')->load($this->getTrackId());
    }
    
    public function getMageBjlTrack() {
        return Mage::getModel('sales/order_shipment_track')->load($this->getBjlTrackId());
    }
    
    public function setMageTrack($track) {
        $this->setTrackId($track->getEntityId());
    }
    
    public function setMageBjlTrack($track) {
        $this->setBjlTrackId($track->getEntityId());
    }
    
    public function loadByMageTrack($track) {
        return $this->load($track->getEntityId(), 'track_id');
    }
}
