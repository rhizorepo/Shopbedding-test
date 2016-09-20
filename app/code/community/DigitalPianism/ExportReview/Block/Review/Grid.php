<?php
/*
Grid to add the export button 
*/

class DigitalPianism_ExportReview_Block_Review_Grid extends Mage_Adminhtml_Block_Review_Grid
{
    protected function _prepareColumns()
    {
	
		$statuses = Mage::getModel('review/review')
            ->getStatusCollection()
            ->load()
            ->toOptionArray();

		$tmpArr = array();
        foreach( $statuses as $key => $status ) {
            $tmpArr[$status['value']] = $status['label'];
        }

        $statuses = $tmpArr;
		if($this->getRequest()->getParam('email')==1){
        $this->addColumn('email', array(
            'header'        => Mage::helper('review')->__('Email'),
            'align'         => 'left',
            'width'         => '100px',
            'filter_index'  => 'rdt.email',
            'index'         => 'email',
            'type'          => 'text',
            'truncate'      => 50,
            'escape'        => true,
        ));		
		} else {
        $this->addColumn('review_id', array(
            'header'        => Mage::helper('review')->__('Review ID'),
            'align'         => 'right',
            'width'         => '50px',
            'filter_index'  => 'rt.review_id',
            'index'         => 'review_id',
        ));

        $this->addColumn('created_at', array(
            'header'        => Mage::helper('review')->__('Created On'),
            'align'         => 'left',
            'type'          => 'datetime',
            'width'         => '100px',
            'filter_index'  => 'rt.created_at',
            'index'         => 'created_at',
        ));

        if( !Mage::registry('usePendingFilter') ) {
            $this->addColumn('status', array(
                'header'        => Mage::helper('review')->__('Status'),
                'align'         => 'left',
                'type'          => 'options',
                'options'       => $statuses,
                'width'         => '100px',
                'filter_index'  => 'rt.status_id',
                'index'         => 'status_id',
            ));
        }

        $this->addColumn('title', array(
            'header'        => Mage::helper('review')->__('Title'),
            'align'         => 'left',
            'width'         => '100px',
            'filter_index'  => 'rdt.title',
            'index'         => 'title',
            'type'          => 'text',
            //'truncate'      => 50, // We remove the truncate to display the entire review title
            'escape'        => true,
        ));

        $this->addColumn('nickname', array(
            'header'        => Mage::helper('review')->__('Nickname'),
            'align'         => 'left',
            'width'         => '100px',
            'filter_index'  => 'rdt.nickname',
            'index'         => 'nickname',
            'type'          => 'text',
            'truncate'      => 50,
            'escape'        => true,
        ));
        $this->addColumn('email', array(
            'header'        => Mage::helper('review')->__('Email'),
            'align'         => 'left',
            'width'         => '100px',
            'filter_index'  => 'rdt.email',
            'index'         => 'email',
            'type'          => 'text',
            'truncate'      => 50,
            'escape'        => true,
        ));
        $this->addColumn('detail', array(
            'header'        => Mage::helper('review')->__('Review'),
            'align'         => 'left',
            'index'         => 'detail',
            'filter_index'  => 'rdt.detail',
            'type'          => 'text',
			//'truncate'      => 50, // We remove the truncate to display the entire review details
            'nl2br'         => true,
            'escape'        => true,
        ));
		
        /**
         * Check is single store mode
         */
        if (!Mage::app()->isSingleStoreMode()) {
            $this->addColumn('visible_in', array(
                'header'    => Mage::helper('review')->__('Visible In'),
                'index'     => 'stores',
                'type'      => 'store',
                'store_view' => true,
            ));
        }

        $this->addColumn('type', array(
            'header'    => Mage::helper('review')->__('Type'),
            'type'      => 'select',
            'index'     => 'type',
            'filter'    => 'adminhtml/review_grid_filter_type',
            'renderer'  => 'adminhtml/review_grid_renderer_type'
        ));

        $this->addColumn('name', array(
            'header'    => Mage::helper('review')->__('Product Name'),
            'align'     =>'left',
            'type'      => 'text',
            'index'     => 'name',
            'escape'    => true
        ));

        $this->addColumn('sku', array(
            'header'    => Mage::helper('review')->__('Product SKU'),
            'align'     => 'right',
            'type'      => 'text',
            'width'     => '50px',
            'index'     => 'sku',
            'escape'    => true
        ));

        $this->addColumn('action',
            array(
                'header'    => Mage::helper('adminhtml')->__('Action'),
                'width'     => '50px',
                'type'      => 'action',
                'getter'     => 'getReviewId',
                'actions'   => array(
                    array(
                        'caption' => Mage::helper('adminhtml')->__('Edit'),
                        'url'     => array(
                            'base'=>'*/catalog_product_review/edit',
                            'params'=> array(
                                'productId' => $this->getProductId(),
                                'customerId' => $this->getCustomerId(),
                                'ret'       => ( Mage::registry('usePendingFilter') ) ? 'pending' : null
                            )
                         ),
                         'field'   => 'id'
                    )
                ),
                'filter'    => false,
                'sortable'  => false
        ));
		
		}
		$this->addRssList('rss/catalog/review', Mage::helper('catalog')->__('Pending Reviews RSS'));
		
		/* Add CSV and Excel export */
		$this->addExportType('*/*/exportCsv', Mage::helper('review')->__('CSV'));
        $this->addExportType('*/*/exportExcel', Mage::helper('review')->__('Excel'));

		// We don't call the Mage_Adminhtml_Block_Review_Grid function as it would rewrite our columns
        return Mage_Adminhtml_Block_Widget_Grid::_prepareColumns();
    }
}
