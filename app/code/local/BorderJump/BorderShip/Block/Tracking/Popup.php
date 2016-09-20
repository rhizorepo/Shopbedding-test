<?php

class BorderJump_BorderShip_Block_Tracking_Popup extends Mage_Shipping_Block_Tracking_Popup
{
    public function getTrackingInfo() {
        $shipments = parent::getTrackingInfo();
        
        $newShipments = array();
        foreach ($shipments as $k => $tracks) {
            $newTracks = array();
            foreach ($tracks as $track) {
                if (is_object($track) and $track->getCarrier() == 'bordership') {
                    $newTracks[] = $track;
                }
            }
            $newShipments[$k] = $newTracks;
        }
        //return $shipments;
        return $newShipments;
    }
}