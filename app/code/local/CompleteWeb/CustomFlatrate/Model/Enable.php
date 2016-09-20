<?php
/**
 * Source for cron frequency 
 *
 * @category    Find
 * @package     Find_Feed
 */
class CompleteWeb_CustomFlatrate_Model_Enable
{
    /**
     * Fetch options array
     *
     * @return array
     */
     public function toOptionArray()
    {
        return array(
            array('value'=>'both', 'label'=> Mage::helper('adminhtml')->__('Both')),
            array('value'=>'only_promotion', 'label'=>Mage::helper('adminhtml')->__('Only Promotion')),
            array('value'=>'only_shipping', 'label'=>Mage::helper('adminhtml')->__('Only Shipping')),
        );
    }
}
