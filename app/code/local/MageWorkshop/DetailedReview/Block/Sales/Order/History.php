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

class MageWorkshop_DetailedReview_Block_Sales_Order_History extends Mage_Sales_Block_Order_History
{

    public function __construct()
    {
        parent::__construct();
        if(Mage::getStoreConfig('detailedreview/settings/enable')) {
            $this->setTemplate('detailedreview/sales/order/history.phtml');
        }
    }
}
