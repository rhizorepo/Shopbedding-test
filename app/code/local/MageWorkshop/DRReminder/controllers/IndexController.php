<?php
/**
 * MageWorkshop
 * Copyright (C) 2016 MageWorkshop <mageworkshophq@gmail.com>
 *
 * @category   MageWorkshop
 * @package    MageWorkshop_DRReminder
 * @copyright  Copyright (c) 2016 MageWorkshop Co. (http://mage-workshop.com)
 * @license    http://opensource.org/licenses/gpl-3.0.html GNU General Public License, version 3 (GPL-3.0)
 * @author     MageWorkshop <mageworkshophq@gmail.com>
 */

class MageWorkshop_DRReminder_IndexController extends Mage_Core_Controller_Front_Action
{

    /**
     * Products for Review
     */
    public function productsAction()
    {
        if(Mage::getStoreConfig('drreminder/settings/remind_enable')) {
            $this->loadLayout();
            $this->renderLayout();
        } else {
            $this->_forward('defaultNoRoute');
        }

    }
}
