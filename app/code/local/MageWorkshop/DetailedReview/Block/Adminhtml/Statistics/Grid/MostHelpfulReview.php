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
class MageWorkshop_DetailedReview_Block_Adminhtml_Statistics_Grid_MostHelpfulReview extends Mage_Adminhtml_Block_Dashboard_Grid
{
    /**
     * @inherit
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('mostHelpfulReviewGrid');
        $this->setDefaultLimit(Mage::getStoreConfig('detailedreview/statistics_options/qty_items_in_most_helpful_grid'));
    }

    /**
     * @inherit
     */
    protected function _prepareCollection()
    {
        if (!Mage::helper('core')->isModuleEnabled('Mage_Reports')) {
            return $this;
        }
        $from = $this->getRequest()->getParam('from') ? $this->getRequest()->getParam('from') : date('d-m-Y', strtotime("-1 month"));
        $to = $this->getRequest()->getParam('to') ? $this->getRequest()->getParam('to') : date('d-m-Y');

        /** @var MageWorkshop_DetailedReview_Model_Mysql4_Review_Collection $reviewsCollection */
        $reviewsCollection = Mage::getModel('review/review')->getCollection()
            ->addDateFromToFilter($from, $to)
            ->addHelpfulInfo()
            ->setOrder('count_helpful', Varien_Data_Collection::SORT_ORDER_DESC)
            ->setPageSize($this->_defaultLimit);

        $reviewsCollection->load();

        $this->setCollection($reviewsCollection);

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
            'header'    => $helper->__('Customer Name'),
            'sortable'  => false,
            'index'     => 'nickname',
        ));

        $this->addColumn('review_cnt', array(
            'header'    => $helper->__('Number of Helpful'),
            'align'     => 'right',
            'width'     => '120',
            'sortable'  => false,
            'index'     => 'count_helpful'
        ));

        $this->addColumn('action',
            array(
                'header'    =>  Mage::helper('customer')->__('Action'),
                'align'     => 'center',
                'width'     => '50',
                'type'      => 'action',
                'getter'    => 'getReviewId',
                'actions'   => array(
                    array(
                        'caption'   => Mage::helper('customer')->__('View'),
                        'url'       => array('base'=> 'adminhtml/catalog_product_review/edit'),
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
        return $this->getUrl('adminhtml/catalog_product_review/edit', array('id' => $row->getReviewId()));
    }
}
