<?php
class ChannelBrain_BizSyncXL_Block_Sales_Order_Grid extends Mage_Adminhtml_Block_Sales_Order_Grid
{
    
    protected function _prepareColumns()
    {
		   $this->addColumn('created_at', array(
	            'header' => Mage::helper('sales')->__('Purchased On'),
	            'index' => 'created_at',
	            'type' => 'datetime',
	            'width' => '100px',
	        ));
	
		 $this->addColumn('real_order_id', array(
	            'header'=> Mage::helper('sales')->__('Order #'),
	            'width' => '80px',
	            'type'  => 'text',
	            'index' => 'increment_id',
	        ));
	
		$this->addColumn('mom_order_id', array(
		    'header' => Mage::helper('sales')->__('MOM Order #'),
		    'width' => '50px',
	        'type'  => 'text',
	        'index' => 'ext_order_id',

		));
		
		  $this->addColumn('billing_name', array(
	            'header' => Mage::helper('sales')->__('Bill to Name'),
	            'index' => 'billing_name',
	        ));

	        $this->addColumn('shipping_name', array(
	            'header' => Mage::helper('sales')->__('Ship to Name'),
	            'index' => 'shipping_name',
	        ));
	
			$this->addColumn('delivery_date2', array(
	            'header' => Mage::helper('adjdeliverydate')->__('Shipping Date'),
	            'index'  => 'delivery_comment',
	            'type'   => 'date',
	            'renderer' => 'adminhtml/widget_grid_column_renderer_date',
	            'filter' => 'adjdeliverydate/adminhtml_filter_delivery', //AdjustWare_Deliverydate_Block_Adminhtml_Filter_Delivery
	            'width'  => '100px', 
	            'value' => '01/01/2011',
	        ));
	
	
	 $this->addColumn('shipping_method', array(
	            'header' => Mage::helper('sales')->__('Shipping Method'),
	            'index' => 'shipping_method',
	             'width' => '50px',
	       ));
	
	    $this->addColumn('delivery_date', array(
            'header' => Mage::helper('adjdeliverydate')->__('Delivery Date'),
            'index'  => 'delivery_date',
            'type'   => 'date',
            'renderer' => 'adminhtml/widget_grid_column_renderer_date',
            'filter' => 'adjdeliverydate/adminhtml_filter_delivery', //AdjustWare_Deliverydate_Block_Adminhtml_Filter_Delivery
            'width'  => '100px', 
        ));

	$this->addColumn('grand_total', array(
        'header' => Mage::helper('sales')->__('G.T. (Purchased)'),
        'index' => 'grand_total',
        'type'  => 'currency',
        'currency' => 'order_currency_code',
    ));

    $this->addColumn('status', array(
        'header' => Mage::helper('sales')->__('Status'),
        'index' => 'status',
        'type'  => 'options',
        'width' => '70px',
        'options' => Mage::getSingleton('sales/order_config')->getStatuses(),
    ));

    if (Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/view')) {
        $this->addColumn('action',
            array(
                'header'    => Mage::helper('sales')->__('Action'),
                'width'     => '50px',
                'type'      => 'action',
                'getter'     => 'getId',
                'actions'   => array(
                    array(
                        'caption' => Mage::helper('sales')->__('View'),
                        'url'     => array('base'=>'*/sales_order/view'),
                        'field'   => 'order_id'
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'stores',
                'is_system' => true,
        ));
    }
//    $this->addRssList('rss/order/new', Mage::helper('sales')->__('New Order RSS'));
//    $this->addExportType('*/*/exportCsv', Mage::helper('sales')->__('CSV'));
//    $this->addExportType('*/*/exportExcel', Mage::helper('sales')->__('Excel XML'));
return;
    //return parent::_prepareColumns();
}
}