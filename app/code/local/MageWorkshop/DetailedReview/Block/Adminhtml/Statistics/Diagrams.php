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
class MageWorkshop_DetailedReview_Block_Adminhtml_Statistics_Diagrams extends Mage_Adminhtml_Block_Widget_Tabs
{
    /**
     * @inherit
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('diagram_tab');
        $this->setDestElementId('diagram_tab_content');
        $this->setTemplate('widget/tabshoriz.phtml');
    }

    /**
     * @inherit
     */
    protected function _prepareLayout()
    {
        /** @var MageWorkshop_DetailedReview_Block_Adminhtml_Statistics_Tab_Activity $activityBlock */
        $activityBlock = $this->getLayout()->createBlock('detailedreview/adminhtml_statistics_tab_activity');
        $this->addTab('activity', array(
            'label'     => $this->__('Activity'),
            'content'   => $activityBlock->toHtml(),
            'active'    => true
        ));
        return parent::_prepareLayout();
    }
}
