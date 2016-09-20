<?php
class BorderJump_BorderShip_Block_Adminhtml_Shipping_Carrier_Tablerate_Grid extends Mage_Adminhtml_Block_Shipping_Carrier_Tablerate_Grid
{
    protected function _prepareCollection()
    {
        /** @var $collection Mage_Shipping_Model_Mysql4_Carrier_Tablerate_Collection */
        $collection = Mage::getResourceModel('bordership/carrier_tablerate_collection');
        $collection->setWebsiteFilter($this->getWebsiteId());
        
        $this->setCollection($collection);
        
        return $this;
    }
    
    protected function _prepareColumns()
    {
        $label = Mage::getSingleton('shipping/carrier_tablerate')
            ->getCode('condition_name_short', $this->getConditionName());
        $this->addColumn('condition_value', array(
            'header'    => $label,
            'index'     => 'condition_value',
        ));

        $this->addColumn('price', array(
            'header'    => Mage::helper('bordership')->__('Shipping Price'),
            'index'     => 'price',
        ));
        
        return Mage_Adminhtml_Block_Widget_Grid::_prepareColumns();
    }
}