<?php
     
class BorderJump_BorderPay_Helper_Response extends Mage_Core_Helper_Abstract
{
    private function _checkResponse($response, $field=null) {
        if ( ! is_array($response)) {
            return false;
        }
        
        if ($field) {
            if ( ! isset($response[$field])) {
                return false;
            }
        }
        
        return true;
    }
    
    public function isEnrolled($response) {
        $field = 'enrolled';
        if ( ! $this->_checkResponse($response, $field)) {
            return false;
        }
        
        $enrolled = $response[$field];
        return $enrolled == 1;
    }
    
    // gets order status from an API response.
    public function getStatus($response) {
        $fields = array('status', 'PAResStatus');
        $field = null;
        foreach ($fields as $_field) {
            if ($this->_checkResponse($response, $_field)) {
                $field = $_field;
                break;
            }
        }
        
        if ($field == null) {
            return false;
        }
        
        $status = $response[$field];
        $returnStatus = null;
        
        $statusCategories = array(
            'pending' => array('pending', 'P'),
            'approved' => array('authorized', 'approved', 'captured', 'complete', 'Y'),
            'declined' => array('declined'),
            'cancelled' => array('cancelled', 'canceled', 'X'),
        );
        
        foreach ($statusCategories as $k => $statuses) {
            if (in_array($status, $statuses)) {
                $status = $k;
                break;
            }
        }
        
        return $status;
    }

    // gets apistatus from an API response.
    public function getApiStatus($response) {
        $fields = array('apistatus');
        $field = null;
        foreach ($fields as $_field) {
            if ($this->_checkResponse($response, $_field)) {
                $field = $_field;
                break;
            }
        }
        
        if ($field == null) {
            return false;
        }
        
        return $response[$field];
    }

}
