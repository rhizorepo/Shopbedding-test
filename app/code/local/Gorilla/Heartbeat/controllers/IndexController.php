<?php
/**
 * Index controller
 * @package 
 */
class Gorilla_Heartbeat_IndexController extends Mage_Core_Controller_Front_Action
{
    const CODE_SUCCESS = 'OK';
    const CODE_ERROR   = 'ERROR';

    protected $_collector;

    /**
     * Index action.
     * Checks if all tests is passes. Returns response code in heders 200 if OK of 500 if something is wrong.
     * Alse returns XML statistics in headers for pingdom in format 
     * <pingdom_http_custom_check>
     *     <status>CODE</status>
     *     <response_time>TIME</response_time>
     * </pingdom_http_custom_check> 
     */
    public function indexAction()
    {
        if (!Mage::getStoreConfig('heartbeat/general/enable')) {
            $this->_redirect('/');
            return;
        }
        $this->_getCollector()->processTests();
        $code = 500;
        $textCode = self::CODE_ERROR;
        if ($this->_getCollector()->isAllPassed()) {
            $code = 200;
            $textCode = self::CODE_SUCCESS;
        }

        $errors = $this->_getCollector()->getErrors();
        $errorCodes = '';
        if (count($errors)) {
            $errorCodes = '<errors>';
            foreach ($errors as $error) {
                $errorCodes .= '<error>' . $error . '</error>';
            }
            $errorCodes .='</errors>';
        }

        $warnings = $this->_getCollector()->getWarnings();
        $warningCodes = '';
        if (count($warnings)) {
            $warningCodes = '<warnings>';
            foreach ($warnings as $warning) {
                $warningCodes .= '<warning>' . $warning . '</warning>';
            }
            $warningCodes .='</warnings>';
        }

        $time = $this->_getCollector()->getPassingTime();
        
        $this->getResponse()
            ->setHttpResponseCode($code)
            ->setHeader('Content-type', 'application/xml', true)
            ->setBody('<?xml version="1.0" encoding="UTF-8" ?><pingdom_http_custom_check><status>' . $textCode . '</status><response_time>' . $time . '</response_time>' . $errorCodes . $warningCodes . '</pingdom_http_custom_check>')
            ;
    }


    /**
     * @return Gorilla_Heartbeat_Model_TestsCollector
     */
    protected function _getCollector()
    {
        if (!$this->_collector) {
            $this->_collector = Mage::getSingleton('heartbeat/testsCollector');
        }
        return $this->_collector;
    }

//    public function testAction()
//    {
//        $observer = new Gorilla_Heartbeat_Model_Observer();
//        print_r($observer->processMailQueue());
//    }
}