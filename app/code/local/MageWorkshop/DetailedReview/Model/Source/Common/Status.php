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
class MageWorkshop_DetailedReview_Model_Source_Common_Status
{
    const STATUS_DISABLED = 0;
    const STATUS_ENABLED  = 1;

    /**
     * @return array
     */
    public static function toOptionArray()
    {
        return array(
            self::STATUS_ENABLED  => Mage::helper('detailedreview')->__('Enabled'),
            self::STATUS_DISABLED => Mage::helper('detailedreview')->__('Disabled'),
        );
    }

}
