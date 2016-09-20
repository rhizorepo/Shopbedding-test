<?php
/**
 * MageWorkshop
 * Copyright (C) 2016 MageWorkshop <mageworkshophq@gmail.com>
 *
 * @category   MageWorkshop
 * @package    MageWorkshop_DRReminder
 * @copyright  Copyright (c) 2016 MageWorkshop Co. (http://mage-workshop.com)
 * @license    http://opensource.org/licenses/gpl-3.0.html GNU General Public License, version 3 (GPL-3.0)
 * @author     MageWorkshop <mageworkshophq@gmail.com>
 */


class MageWorkshop_DRReminder_Block_Adminhtml_Reminder_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('reminderGrid')
            ->setDefaultSort('id')
            ->setDefaultDir('DESC')
            ->setSaveParametersInSession(true)
            ->setUseAjax(true);
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('drreminder/reminder')->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $helper = Mage::helper('drreminder');

        $this->addColumn('id', array(
            'header'    => $helper->__('ID'),
            'width'     => '50px',
            'index'     => 'id',
            'type'  => 'number',
        ));

        $this->addColumn('customer_name', array(
            'header'    => $helper->__('Customer Name'),
            'index'     => 'customer_name'
        ));

        $this->addColumn('email', array(
            'header'    => $helper->__('Email'),
            'index'     => 'email'
        ));

        $this->addColumn('increment_id', array(
            'header'    => $helper->__('Order #'),
            'index'     => 'increment_id'
        ));

        $this->addColumn('creating_date', array(
            'header'    => $helper->__('Created At'),
            'type'    => 'date',
            'index'     => 'creating_date'
        ));
        $this->addColumn('expiration_date', array(
            'header'    => $helper->__('Expiration Time'),
            'type'    => 'date',
            'index'     => 'expiration_date'
        ));

        $this->addColumn('status',
            array(
                'header'  => $helper->__('Status'),
                'align'   => 'left',
                'width'   => '80px',
                'index'   => 'status',
                'type'    => 'options',
                'options' => Mage::getModel('drreminder/source_reminder_status')->toOptionArray()
            ));

        $this->addColumn('action',
            array(
                'header'    =>  $helper->__('Action'),
                'width'     => '80px',
                'type'      => 'action',
                'getter'    => 'getId',
                'actions'   => array(
                    array(
                        'caption'   => $helper->__('Edit'),
                        'url'       => array('base' => '*/*/edit'),
                        'field'     => 'id',
                    ),
                    array(
                        'caption'   => $helper->__('View'),
                        'url'       => array('base' => '*/*/view'),
                        'field'     => 'id',
                    ),
                    array(
                        'caption'   => $helper->__('Hold'),
                        'url'       => array('base' => '*/*/hold'),
                        'field'     => 'id',
                        'confirm'   => $helper->__('Hold this Review Reminder?'),
                    ),
                    array(
                        'caption'   => $helper->__('Unhold'),
                        'url'       => array('base' => '*/*/unhold'),
                        'field'     => 'id',
                        'confirm'   => $helper->__('Unhold this Review Reminder?'),
                    ),
                    array(
                        'caption'   => $helper->__('Delete'),
                        'url'       => array('base' => '*/*/delete'),
                        'field'     => 'id',
                        'confirm'   => $helper->__('Are you sure?'),
                    ),
                    array(
                        'caption'   => $helper->__('Send now'),
                        'url'       => array('base' => '*/*/send'),
                        'field'     => 'id',
                        'confirm'   => $helper->__('Are you sure you want to sent this Review Reminder immediately?'),
                    ),
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
        $this->getMassactionBlock()->setFormFieldName('reminder_ids');
        $helper = Mage::helper('drreminder');

        $this->getMassactionBlock()->addItem('delete',
            array(
                'label'   => $helper->__('Delete'),
                'url'     => $this->getUrl('*/*/massDelete'),
                'confirm' => $helper->__('Are you sure?')
            ));
        
        $this->getMassactionBlock()->addItem('status',
            array(
                'label'=> $this->__('Change status'),
                'url'  => $this->getUrl('*/*/massStatus', array('_current'=>true)),
                'additional' => array(
                    'visibility' => array(
                        'name'   => 'status',
                        'type'   => 'select',
                        'class'  => 'required-entry',
                        'label'  => $this->__('Status'),
                        'values' => Mage::getModel('drreminder/source_reminder_status')->toOptionArray()
                    )
                )
            )
        );
        Mage::dispatchEvent('drreminder_adminhtml_reminder_grid_prepare_massaction', array('block' => $this));
        return $this;
    }
    
    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/view', array('id' => $row->getId()));
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current'=>true));
    }
}
