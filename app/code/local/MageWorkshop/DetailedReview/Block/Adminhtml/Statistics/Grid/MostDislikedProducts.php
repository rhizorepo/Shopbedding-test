<?php
/**
 * MageWorkshop
 * Copyright (C) 2016 MageWorkshop <mageworkshophq@gmail.com>
 *
 * @category   MageWorkshop
 * @package    MageWorkshop_DetailedReview
 * @copyright  Copyright (c) 2016 MageWorkshop Co. (http://mage-workshop.com)
 * @license    http://opensource.org/licenses/gpl-3.0.html GNU General Public License, version 3 (GPL-3.0)
 * @author     MageWorkshop <mageworkshophq@gmail.com>
 */
class MageWorkshop_DetailedReview_Block_Adminhtml_Statistics_Grid_MostDislikedProducts extends Mage_Adminhtml_Block_Dashboard_Grid
{

    /**
     * @inherit
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('mostDislikedProducts');
        $this->setDefaultLimit(Mage::getStoreConfig('detailedreview/statistics_options/qty_items_in_disliked_grid'));
    }

    /**
     * @inherit
     */
    protected function _prepareCollection()
    {
        if (!Mage::helper('core')->isModuleEnabled('Mage_Reports')) {
            return $this;
        }
        $collection = Mage::getResourceModel('reports/review_product_collection')
            ->joinReview()
            ->addAttributeToSort('avg_rating', Zend_Db_Select::SQL_ASC );
//        'review_cnt', 'last_created', 'avg_rating', 'avg_rating_approved'

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * Prepares page sizes for dashboard grid with las 5 orders
     *
     * @return void
     */
    protected function _preparePage()
    {
        $this->getCollection()->setPageSize($this->getParam($this->getVarNameLimit(), $this->_defaultLimit));
        // Remove count of total orders $this->getCollection()->setCurPage($this->getParam($this->getVarNamePage(), $this->_defaultPage));
    }

    /**
     * @return $this
     */
    protected function _prepareColumns()
    {
        $helper = Mage::helper('detailedreview');
        $this->addColumn('name', array(
            'header'    => $helper->__('Product Name'),
            'sortable'  => false,
            'index'     => 'name',
        ));

        $this->addColumn('avg_rating', array(
            'header'    => $helper->__('Avg. Rating'),
            'align'     => 'center',
            'width'     => '120',
            'sortable'  => false,
            'index'     => 'avg_rating',
            'renderer'      => 'detailedreview/adminhtml_statistics_grid_renderer_rating'
        ));

        $this->addColumn('action',
            array(
                'header'    =>  Mage::helper('customer')->__('Action'),
                'align'     => 'center',
                'width'     => '50',
                'type'      => 'action',
                'getter'    => 'getId',
                'actions'   => array(
                    array(
                        'caption'   => Mage::helper('customer')->__('View'),
                        'url'       => array('base'=> 'adminhtml/catalog_product/edit'),
                        'field'     => 'id'
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'stores',
                'is_system' => true,
        ));

        $this->setFilterVisibility(false);
        $this->setPagerVisibility(false);

        return parent::_prepareColumns();
    }

    /**
     * @inherit
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('adminhtml/catalog_product/edit', array('id' => $row->getId()));
    }
}
