<?php
/**
 * database test model. 
 * @package
 * @deprecated after 0.0.2
 */
class Gorilla_Heartbeat_Model_Tests_Database 
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
        //$write = Mage::getSingleton('core/resource')->getConnection('core_write');
        //$writeItem = $write->query("INSERT INTO core_config_data (`value`, `path`) VALUES ('test', 'heartbeat/test/test') ON DUPLICATE KEY UPDATE `value` = 'test'");

        $read = Mage::getSingleton('core/resource')->getConnection('core_read');
        $readItem = $read->query('SELECT * FROM `core_config_data` LIMIT 1')->fetch();
        
        if ($readItem /*&& $writeItem*/) {
            return true;
        }

        Mage::helper('heartbeat')->log('Database Test Error. Cant read from database.', null, 'gorilla_heartbeat.log');
        return false;
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
        return 'Database is not reachable. Please check the database credentials.';
    }

    public function process(){}
}