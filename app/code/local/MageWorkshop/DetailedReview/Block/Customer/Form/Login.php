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
class MageWorkshop_DetailedReview_Block_Customer_Form_Login extends Mage_Customer_Block_Form_Login
{
    /**
     * @inherit
     */
    protected function _prepareLayout()
    {
        return $this;
    }

    /**
     * @return MageWorkshop_DetailedReview_Helper_Data
     */
    public function getHelper($type)
    {
        return Mage::helper($type);
    }
}
