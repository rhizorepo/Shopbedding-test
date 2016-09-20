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
 * @package     Hunter_Crawler
 * @copyright   Copyright (c) 2015
 * @license     http://opensource.org/licenses/mit-license.php MIT License
 * @author      Roman Tkachenko roman.tkachenko@huntersconsult.com
 */
class Hunter_Crawler_Block_Result_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('grid_id');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('desc');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('hunter_crawler/result')->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('entity_id',
            array(
               'header' => $this->__('Entity Id'),
               'index'  => 'entity_id'
            )
        );
        $this->addColumn('date',
            array(
                'header'        => $this->__('Date'),
                'index'         => 'date',
                'type'          => 'datetime',
                'filter_time'   => true,
            )
        );
        $this->addColumn('page_title',
            array(
               'header' => $this->__('Page Title'),
               'index'  => 'page_title'
            )
        );
        $this->addColumn('page_url',
            array(
               'header'=> $this->__('Page URL'),
               'index' => 'page_url'
            )
        );
        $this->addColumn('first_request',
            array(
               'header'=> $this->__('First Request Time (seconds)'),
               'index' => 'first_request'
            )
        );
        $this->addColumn('second_request',
            array(
               'header'=> $this->__('Second Request Time (seconds)'),
               'index' => 'second_request'
            )
        );

        $this->addExportType('*/*/exportCsv', $this->__('CSV'));
        $this->addExportType('*/*/exportExcel', $this->__('Excel XML'));
        
        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        $modelPk = Mage::getModel('hunter_crawler/result')->getResource()->getIdFieldName();
        $this->setMassactionIdField($modelPk);
        $this->getMassactionBlock()->setFormFieldName('ids');
         $this->getMassactionBlock()->setUseSelectAll(true);
        $this->getMassactionBlock()->addItem('delete', array(
             'label'=> $this->__('Delete'),
             'url'  => $this->getUrl('*/*/massDelete'),
        ));
        return $this;
    }
}
