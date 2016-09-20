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
class MageWorkshop_DetailedReview_Block_Helper extends Mage_Review_Block_Helper
{
    /**
     * @inherit
     */
    public function __construct()
    {
        if (Mage::getStoreConfig('detailedreview/settings/enable')) {
            $this->_availableTemplates['default'] = 'detailedreview/review/helper/summary.phtml';
            $this->_availableTemplates['short']   = 'detailedreview/review/helper/summary_short.phtml';
        }
        parent::__construct();
    }
}
