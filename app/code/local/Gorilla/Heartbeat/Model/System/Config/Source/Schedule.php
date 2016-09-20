<?php
class Gorilla_Heartbeat_Model_System_Config_Source_Schedule
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => '0', 'label' => Mage::helper('core')->__('Each hour')),
            array('value' => '1', 'label' => Mage::helper('core')->__('Each 2 hours')),
            array('value' => '2', 'label' => Mage::helper('core')->__('Each 6 hours')),
            array('value' => '3', 'label' => Mage::helper('core')->__('Each 12 hours')),
            array('value' => '4', 'label' => Mage::helper('core')->__('Each 24 hours')),
        );
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return array(
            '0' => Mage::helper('core')->__('1'),
            '1' => Mage::helper('core')->__('2'),
            '2' => Mage::helper('core')->__('6'),
            '3' => Mage::helper('core')->__('12'),
            '4' => Mage::helper('core')->__('24'),
        );
    }
}