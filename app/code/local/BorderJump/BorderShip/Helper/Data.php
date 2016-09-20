<?php
     
class BorderJump_BorderShip_Helper_Data extends Mage_Shipping_Helper_Data
{
    protected function _getTrackingUrl($key, $model, $method = 'getId') {
        if (empty($model)) {
            $param = array($key => ''); // @deprecated after 1.4.0.0-alpha3
        } else if (!is_object($model)) {
            $param = array($key => $model); // @deprecated after 1.4.0.0-alpha3
        } else {
            $param = array(
                'hash' => Mage::helper('core')->urlEncode("{$key}:{$model->$method()}:{$model->getProtectCode()}")
            );
        }
        $storeId = is_object($model) ? $model->getStoreId() : null;
        $storeModel = Mage::app()->getStore($storeId);
        
        if(Mage::getSingleton('admin/session')->isLoggedIn()) {
            return $storeModel->getUrl('bordership/tracking/popup', $param);
        } else {
            return $storeModel->getUrl('shipping/tracking/popup', $param);
        }
    }
    
}
