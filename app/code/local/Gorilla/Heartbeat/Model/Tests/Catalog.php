<?php
/**
 * catalog test model. 
 * @package
 */
class Gorilla_Heartbeat_Model_Tests_Catalog 
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
        $collection = Mage::getModel('catalog/product')->getCollection();
        $collection->getSelect()->limit(1);
        if (is_int($collection->count())) {
            return true;
        }

        Mage::helper('heartbeat')->log('Catalog Test Error. Catalog is empty.', null, 'gorilla_heartbeat.log');
        return false;
    }

    public function process()
    {
        $collection = Mage::getModel('catalog/product')->getCollection();
        $collection->getSelect()->limit(1);
        if (!is_int($collection->count())) {
            Mage::helper('heartbeat')->log('Catalog Test Error. Catalog is empty.', null, $this->_errorLog);
            $this->addError('Catalog Test Error. Catalog is empty.');
        }
    }

    public function getRecommendations() {
        return 'Magento catalog is not reachable. Please check the database integrity.';
    }
}