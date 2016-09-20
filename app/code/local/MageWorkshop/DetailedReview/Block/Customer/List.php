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
class MageWorkshop_DetailedReview_Block_Customer_List extends Mage_Review_Block_Customer_List
{
    /**
     * @inherit
     */
    protected function _toHtml()
    {
        /** @var MageWorkshop_DetailedReview_Helper_Data $helper */
        $helper = $this->helper('detailedreview');
        $helper->applyTheme($this);
        return parent::_toHtml();
    }
}
