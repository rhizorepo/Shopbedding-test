<?php
/**
 * Api test model. 
 * @package 
 */
class Gorilla_Heartbeat_Model_Tests_Api 
    extends Gorilla_Heartbeat_Model_Tests_Abstract
    implements Gorilla_Heartbeat_Model_TestsInterface
{
    /**
     * Returns true if test enabled
     * @return boolean
     * @deprecated after 0.0.2
     */
    public function isEnabled()
    {
        return true;
    }
    
    /**
     * Returns true if test passed
     * @return boolean
     * @deprecated after 0.0.2
     */
    public function isPassed()
    {
        foreach ($this->_collectActiveUrls() as $url) {
            if (! $this->_request($url) && strstr($url, 'http')) {
                Mage::helper('heartbeat')->log('Api Test Error. Can\'t access url: ' . $url, null, 'gorilla_heartbeat.log');
                return false;
            }
        }
        
        return true;
    }
    
    public function process()
    {
        foreach ($this->_collectActiveUrls() as $url) {
            if (! $this->_request($url) && strstr($url, 'http')) {
                Mage::helper('heartbeat')->log('Api Test Error. Can\'t access url: ' . $url, null, $this->_warningLog);
                $this->addWarning('Api Test Error. Can\'t access url: ' . $url);
            }
        }
    }
    
    /**
     * Making request to the $url
     * @param string $url
     * @param string $request
     * @return string
     */
    private function _request($url, $request = '')
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
        $responseBody = curl_exec($ch);
        curl_close($ch);
        
        return $responseBody;
    }
    
    /**
     * Collecting active magento third-party integrations. Returns array of urls.
     * @return array
     */
    private function _collectActiveUrls()
    {
        $urls = array();
        
        if (Mage::getStoreConfig('carriers/ups/active')) {
            if (Mage::getStoreConfig('carriers/ups/type') == 'UPS_XML') {
                $urls[] = Mage::getStoreConfig('carriers/ups/gateway_xml_url');
            }
            if (Mage::getStoreConfig('carriers/ups/type') == 'UPS') {
                $urls[] = Mage::getStoreConfig('carriers/ups/gateway_url');
            }
            $urls[] = Mage::getStoreConfig('carriers/ups/tracking_xml_url');
            
        }

        if (Mage::getStoreConfig('carriers/usps/active')) {
            $urls[] = Mage::getStoreConfig('carriers/usps/gateway_url');
            $urls[] = Mage::getStoreConfig('carriers/usps/gateway_secure_url');
            
        }

        if (Mage::getStoreConfig('carriers/fedex/active')) {
            $urls[] = 'https://wsbeta.fedex.com:443/web-services/rate';
            $urls[] = 'https://ws.fedex.com:443/web-services/rate';
            
        }

        if (Mage::getStoreConfig('carriers/dhl/active')) {
            $urls[] = Mage::getStoreConfig('carriers/dhl/gateway_url');
            
        }

        if (Mage::getStoreConfig('payment/authorizenet/active')) {
            if (Mage::getStoreConfig('payment/authorizenet_directpost/active')) {
                $urls[] = Mage::getStoreConfig('payment/authorizenet_directpost/cgi_url');
            } else {
                $urls[] = Mage::getStoreConfig('payment/authorizenet/cgi_url');
            }
        }

        if (Mage::getStoreConfig('payment/ogone/active')) {
            $urls[] = Mage::getStoreConfig('payment/ogone/ogone_gateway');  
        }
        
        return $urls;
    }

    public function getRecommendations() {
        return 'One of the APIs in not reachable.';
    }
}