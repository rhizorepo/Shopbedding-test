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
class MageWorkshop_DetailedReview_Block_Adminhtml_Statistics extends Mage_Adminhtml_Block_Template
{
    /**
     * @inherit
     */
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('detailedreview/index.phtml');
    }

    /**
     * @inherit
     */
    protected function _prepareLayout()
    {
        $this->setChild('mostReviewedProducts',
            $this->getLayout()->createBlock('detailedreview/adminhtml_statistics_grid_mostReviewedProducts')
        );
        $this->setChild('mostLikedProducts',
            $this->getLayout()->createBlock('detailedreview/adminhtml_statistics_grid_mostLikedProducts')
        );
        $this->setChild('mostDislikedProducts',
            $this->getLayout()->createBlock('detailedreview/adminhtml_statistics_grid_mostDislikedProducts')
        );
        $this->setChild('mostActiveUsers',
            $this->getLayout()->createBlock('detailedreview/adminhtml_statistics_grid_mostActiveCustomers')
        );
        $this->setChild('mostHelpfulReview',
            $this->getLayout()->createBlock('detailedreview/adminhtml_statistics_grid_mostHelpfulReview')
        );

        $this->setChild('diagrams',
            $this->getLayout()->createBlock('detailedreview/adminhtml_statistics_diagrams')
        );

        parent::_prepareLayout();
    }
}
