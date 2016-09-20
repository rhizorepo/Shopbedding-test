<?php
class Gorilla_Heartbeat_Model_System_Config_Source_Tests
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        /** @var Gorilla_Heartbeat_Model_TestsCollector $helper */
        $collector = Mage::getModel('Gorilla_Heartbeat_Model_TestsCollector');
        $tests = $collector->getAllTests();
        $returnArray = array();
        foreach ($tests as $code => $data) {
            $returnArray[] = array('value' => $code, 'label' => $data['label']);
        }
        return $returnArray;
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        /** @var Gorilla_Heartbeat_Model_TestsCollector $helper */
        $collector = Mage::getModel('Gorilla_Heartbeat_Model_TestsCollector');
        $tests = $collector->getAllTests();
        $returnArray = array();
        foreach ($tests as $code => $data) {
            $returnArray[$code] = $data['label'];
        }
        return $returnArray;
    }
}