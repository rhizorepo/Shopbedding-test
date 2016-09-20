<?php

class Shopbedding_Canonical_Block_Adminhtml_Rule_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
  public function __construct()
  {
      parent::__construct();
      $this->setId('shop_canonical_grid');
      $this->setSaveParametersInSession(true);
  }

  protected function _prepareCollection()
  {
      $collection = Mage::getModel('shop_canonical/rule')->getCollection();
      $this->setCollection($collection);
      return parent::_prepareCollection();
  }

  protected function _prepareColumns()
  {
      $this->addColumn('id', array(
          'header'    => Mage::helper('shop_canonical')->__('ID'),
          'align'     =>'right',
          'width'     => '50px',
          'index'     => 'id',
      ));

      $this->addColumn('source', array(
          'header'    => Mage::helper('shop_canonical')->__('From'),
          'width'     => '300px',
          'align'     => 'left',
          'index'     => 'source',
      ));
      
      $this->addColumn('target', array(
			'header'    => Mage::helper('shop_canonical')->__('To'),
			'width'     => '300px',
			'index'     => 'target',
      ));

      $this->addColumn('created_time', array(
			'header'    => Mage::helper('shop_canonical')->__('Created at'),
			'width'     => '100px',
			'index'     => 'created_time',
      ));
      
      $this->addColumn('update_time', array(
			'header'    => Mage::helper('shop_canonical')->__('Update at'),
			'width'     => '100px',
			'index'     => 'update_time',
      ));
      

      $this->addColumn('action',
            array(
                'header'    =>  Mage::helper('shop_canonical')->__('Action'),
                'width'     => '100',
                'type'      => 'action',
                'getter'    => 'getId',
                'actions'   => array(
                    array(
                        'caption'   => Mage::helper('shop_canonical')->__('Edit'),
                        'url'       => array('base'=> '*/*/edit'),
                        'field'     => 'id'
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'stores',
                'is_system' => true,
      ));

      return parent::_prepareColumns();
  }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('id');
        $this->getMassactionBlock()->setFormFieldName('shop_canonical');

        $this->getMassactionBlock()->addItem('delete', array(
             'label'    => Mage::helper('shop_canonical')->__('Delete'),
             'url'      => $this->getUrl('*/*/massDelete'),
             'confirm'  => Mage::helper('shop_canonical')->__('Are you sure?')
        ));

        return $this;
    }

  public function getRowUrl($row)
  {
      return $this->getUrl('*/*/edit', array('id' => $row->getId()));
  }

}