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
class MageWorkshop_DetailedReview_Adminhtml_Mageworkshop_Detailedreview_ProsController extends MageWorkshop_DetailedReview_Controller_Adminhtml_Common
{
    protected $_entityType = MageWorkshop_DetailedReview_Model_Source_EntityType::PROS;

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('catalog/reviews_ratings/reviews/pros');
    }
}

