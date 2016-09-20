<?php

class BorderJump_BorderPay_Model_Currency_Import_BorderPay extends Mage_Directory_Model_Currency_Import_Abstract
{
    protected $_messages = array();
    
    protected function _convert($currencyFrom, $currencyTo, $retry=0)
    {
        $borderpay = Mage::getSingleton('borderpay/method_borderpay');
        $apiClient = Mage::getModel('apiclient/apiclient', $borderpay->getConfigData('api_url'));
        $apiClient->setKey($borderpay->getConfigData('api_key'));
        $apiClient->setSecret($borderpay->getConfigData('api_secret'));
        
        try {
            $method = "/merchant/currency/convert";
            $query = array(
                'to' => $currencyTo,
                'from' => $currencyFrom
            );
            $response = $apiClient->call($method, 'POST', $query);
            
            if( ! $response) {
                $this->_messages[] = Mage::helper('directory')->__('Cannot retrieve rate from BorderPay.');
                return null;
            }
            return (float) $response;
        }
        catch (Exception $e) {
            if( $retry == 0 ) {
                $this->_convert($currencyFrom, $currencyTo, 1);
            } else {
                $this->_messages[] = Mage::helper('directory')->__('Cannot retrieve rate from BorderPay.');
            }
        }
    }
}
