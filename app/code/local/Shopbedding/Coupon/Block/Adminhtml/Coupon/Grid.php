<?php
class Shopbedding_Coupon_Block_Adminhtml_Coupon_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
  public function __construct()
  {
      parent::__construct();
      $this->setId('couponGrid');
      $this->setDefaultSort('coupon_id');
      $this->setDefaultDir('ASC');
      $this->setSaveParametersInSession(true);
  }

  protected function _prepareCollection()
  {
      $collection = Mage::getModel('sales/order')->getCollection()->addAttributeToFilter('coupon_code', array('neq' => ''));
      $this->setCollection($collection);
      return parent::_prepareCollection();
  }

  protected function _prepareColumns()
  {
     $helper = Mage::helper('coupon');
     $currency = (string) Mage::getStoreConfig(Mage_Directory_Model_Currency::XML_PATH_CURRENCY_BASE);
		
	   $this->addColumn('coupon_code', array(
          'header'    => Mage::helper('coupon')->__('Coupon ID'),
          'align'     =>'left',
          'index'     => 'coupon_code',
      ));
	  
	  $this->addColumn('increment_id', array(
            'header'=> Mage::helper('coupon')->__('Order No'),
            'type'  => 'text',
            'index' => 'increment_id',
        ));
	  $this->addColumn('created_at', array(
          'header'    => Mage::helper('coupon')->__('Order Date'),
          'align'     =>'left',
		  'type'      => 'datetime',
          'index'     => 'created_at',
      ));
	  $this->addColumn('base_grand_total', array(
          'header'    => Mage::helper('coupon')->__('Order Total'),
          'align'     =>'left',
		  'type'      => 'currency',		  
          'index'     => 'base_grand_total',
      ));
	   $this->addColumn('base_discount_amount', array(
          'header'    => Mage::helper('coupon')->__('Discount Amount'),
          'align'     =>'left',
		  'type'      => 'currency',		  
          'index'     => 'base_discount_amount',
      ));
	   $this->addColumn('customer_firstname', array(
          'header'    => Mage::helper('coupon')->__('Customer First Name'),
          'align'     =>'left',
          'index'     => 'customer_firstname',
      ));
	   $this->addColumn('customer_lastname', array(
          'header'    => Mage::helper('coupon')->__('Customer Last Name'),
          'align'     =>'left',
          'index'     => 'customer_lastname',
      ));	  
	   $this->addColumn('customer_email', array(
          'header'    => Mage::helper('coupon')->__('Customer Email'),
          'align'     =>'left',
          'index'     => 'customer_email',
      ));
		
	  $this->addExportType('*/*/exportCsv', Mage::helper('coupon')->__('CSV'));
	  $this->addExportType('*/*/exportXml', Mage::helper('coupon')->__('XML'));

      return parent::_prepareColumns();
  }
   public function getRowUrl($row)
  {
      return $this->getUrl('adminhtml/sales_order/view/', array('order_id' => $row->getId()));
  }
  

}