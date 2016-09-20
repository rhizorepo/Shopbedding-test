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
class MageWorkshop_DetailedReview_Block_Review_Form extends Mage_Review_Block_Form
{
    /**
     * @var Mage_Catalog_Model_Category $_category
     */
    protected $_category;
    protected $_prosConsCollection = array();

    /**
     * @inherit
     */
    protected function _toHtml()
    {
        Mage::helper('detailedreview')->applyTheme($this);
        return parent::_toHtml();
    }

    /**
     * @param string $entityType
     * @return Varien_Data_Collection_Db
     */
    public function getProsConsCollection($entityType)
    {
        if(!array_key_exists($entityType, $this->_prosConsCollection)) {
            if(empty($this->_category)) {
                $helper = Mage::helper('detailedreview');
                $this->_category = $helper->getCategoryWithConfig(null, 'use_parent_proscons_settings');
            }
            $class = MageWorkshop_DetailedReview_Model_Source_EntityType::getClassNameByType($entityType);
            $collection = Mage::getModel('detailedreview/review_proscons')->getCollection()
                ->setType($entityType)
                ->addFieldToFilter('status', MageWorkshop_DetailedReview_Model_Source_Common_Status::STATUS_ENABLED)
                ->addStoreFilter();
            $collection->addFieldToFilter('main_table.entity_id', array(
                'in' => explode(',', $this->_category->getData($class))
            ));
           $this->_prosConsCollection[$entityType] = $collection;
        }
        Mage::dispatchEvent('detailedreview_review_prosconscollection', array('collection' => $this->_prosConsCollection[$entityType]));
        return $this->_prosConsCollection[$entityType];
    }
}
