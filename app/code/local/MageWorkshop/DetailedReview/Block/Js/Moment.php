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
class MageWorkshop_DetailedReview_Block_Js_Moment extends Mage_Core_Block_Template
{
    /**
     * @inherit
     */
    protected function _toHtml()
    {
        if (Mage::getStoreConfig('detailedreview/datetime_options/enable_to_set_timezone')) {
            return parent::_toHtml();
        }
    }
}
