<?php
/**
 * index test model. 
 * @package
 * @deprecated after 0.0.2
 */
class Gorilla_Heartbeat_Model_Tests_Index 
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
        $read = Mage::getSingleton('core/resource')->getConnection('core_read');
        $indexes = $read->query("SELECT * FROM `index_process` WHERE `status` = 'require_reindex'")->fetch();
        
        if (!$indexes) {
            return true;
        }

        Mage::helper('heartbeat')->log('Index Test Error. Reindex required for one or mode indexes.', null, 'gorilla_heartbeat.log');
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
        return 'Indexes are invalidated. Please try to make reindex.';
    }

    public function process(){}
}