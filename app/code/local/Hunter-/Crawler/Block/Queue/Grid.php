<?php
/**
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the MIT License
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/mit-license.php
 * 
 * @category    Hunter
 * @category    Hunter_Crawler
 * @copyright   Copyright (c) 2015
 * @license     http://opensource.org/licenses/mit-license.php MIT License
 * @author      Roman Tkachenko roman.tkachenko@huntersconsult.com
 */
class Hunter_Crawler_Block_Queue_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('grid_id');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('asc');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('hunter_crawler/queue')->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('entity_id',
            array(
                'header'    => $this->__('Entity Id'),
                'index'     => 'entity_id'
            )
        );
        $this->addColumn('entity_type',
            array(
                'header'    => $this->__('Entity Type'),
                'index'     => 'entity_type',
                'type'      => 'options',
                'options'   => Mage::getModel('hunter_crawler/source_entitytype')->toOptionArray(),
            )
        );
        $this->addColumn('page_key',
            array(
                'header'    => $this->__('Page URL'),
                'index'     => 'page_key'
            )
        );
        $this->addColumn('date_add',
            array(
                'header'        => $this->__('Date added'),
                'index'         => 'date_add',
                'type'          => 'datetime',
                'filter_time'   => true,
            )
        );
        $this->addColumn('status',
            array(
                'header'    => $this->__('Status'),
                'renderer'  => 'hunter_crawler/queue_renderer_status',
                'filter' => false
            )
        );

        $this->addExportType('*/*/exportCsv', $this->__('CSV'));

        $this->addExportType('*/*/exportExcel', $this->__('Excel XML'));
        
        return parent::_prepareColumns();
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }

    protected function _prepareMassaction()
    {
        $modelPk = Mage::getModel('hunter_crawler/queue')->getResource()->getIdFieldName();
        $this->setMassactionIdField($modelPk);
        $this->getMassactionBlock()->setFormFieldName('ids');
        $this->getMassactionBlock()->setUseSelectAll(true);
        $this->getMassactionBlock()->addItem('delete', array(
             'label'=> $this->__('Delete'),
             'url'  => $this->getUrl('*/*/massDelete'),
        ));
        $this->getMassactionBlock()->addItem('run_refresh', array(
             'label'=> $this->__('Run refresh'),
             'url'  => $this->getUrl('*/*/massRunRefresh'),
        ));
        return $this;
    }
}
