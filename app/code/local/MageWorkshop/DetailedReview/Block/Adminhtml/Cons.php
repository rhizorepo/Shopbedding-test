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
class MageWorkshop_DetailedReview_Block_Adminhtml_Cons extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    /**
     * @inherit
     */
    public function __construct()
    {
        $this->_controller = 'adminhtml_cons';
        $this->_blockGroup = 'detailedreview';
        $this->_headerText = $this->__('Cons Management');
        $this->_addButtonLabel = $this->__('Add Cons');
        parent::__construct();
    }
}
