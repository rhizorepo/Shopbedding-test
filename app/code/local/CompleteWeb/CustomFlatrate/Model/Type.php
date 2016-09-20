<?php
/**
 * Source for cron frequency 
 *
 * @category    Find
 * @package     Find_Feed
 */
class CompleteWeb_CustomFlatrate_Model_Type
{
    /**
     * Fetch options array
     *
     * @return array
     */
     public function toOptionArray()
    {
        return array(
            array('value'=>'order', 'label'=> Mage::helper('adminhtml')->__('Per Order')),
            array('value'=>'product', 'label'=>Mage::helper('adminhtml')->__('Per Product')),
            array('value'=>'category', 'label'=>Mage::helper('adminhtml')->__('Per Category')),
        );
    }
}
