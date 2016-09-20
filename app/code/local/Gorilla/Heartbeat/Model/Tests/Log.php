<?php
/**
 * Logs test model. 
 * @package
 * @deprecated after 0.0.2
 */
class Gorilla_Heartbeat_Model_Tests_Log
    extends Mage_Core_Model_Abstract 
    implements Gorilla_Heartbeat_Model_TestsInterface
{
    /**
     * Returns true if test enabled
     * @return boolean
     * @deprecated after 0.0.2
     */
    public function isEnabled()
    {
        return false;
        /* this test is deprecated */
    }
    
    /**
     * Returns true if test passed
     * @return boolean
     * @deprecated after 0.0.2
     */
    public function isPassed()
    {
        
        $logFile = Mage::getStoreConfig('dev/log/exception_file');
        $logDir = Mage::getBaseDir('var') . DS . 'log';
        if (file_exists($logDir . DS . $logFile) && file_get_contents($logDir. DS .$logFile)) {
            return false;
        }

        Mage::helper('heartbeat')->log('Log Test Error. Exception.log is not empty.', null, 'gorilla_heartbeat.log');
        return true;
    }
    
    /**
     * Returns result of the rest
     * @return mixed
     */
    public function getResult()
    {
        return true;
    }

    public function getRecommendations() {
        return 'You have exception log in your var/log folder.';
    }

    public function process(){}
}