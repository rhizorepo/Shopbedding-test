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

/**
 * Class MageWorkshop_DetailedReview_Model_Review_Proscons
 *
 * @method string getEntityType()
 * @method MageWorkshop_DetailedReview_Model_Review_Proscons setEntityType(string $entityType)
 * @method string getName()
 * @method MageWorkshop_DetailedReview_Model_Review_Proscons setName(string $name)
 * @method string getStatus()
 * @method MageWorkshop_DetailedReview_Model_Review_Proscons setStatus(int $status)
 * @method MageWorkshop_DetailedReview_Model_Review_Proscons setStoreIds(array $storeIds)
 * @method MageWorkshop_DetailedReview_Model_Review_Proscons setWroteBy(string $writtenBy)
 */
class MageWorkshop_DetailedReview_Model_Review_Proscons extends Mage_Core_Model_Abstract
{
    public function __construct()
    {
        $this->_init('detailedreview/review_proscons');
    }

    /**
     * @return null|array
     */
    public function getStoreIds()
    {
        if (!$ids = $this->_getData('store_ids')) {
            $this->loadStoreIds();
            $ids = $this->getData('store_ids');
        }
        return $ids;
    }

    public function loadStoreIds()
    {
        $this->_getResource()->loadStoreIds($this);
    }

    /**
     * @return Mage_Core_Block_Template
     */
    public function getRendererBlock()
    {
        /** @var MAge_Core_Block_Template $block */
        $block = Mage::getModel('core/layout')->createBlock('core/template');
        return $block->assign('entity', $this);
    }
}
