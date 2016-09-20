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
abstract class Mage_Detailedreview_Model_Category_Attribute_Source_ProsConsAbstract
    extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{
    protected $_entityType;
    protected $_optionsArray = array();

    /**
     * @return array
     */
    public function getAllOptions() {
        $helper = Mage::helper('core');
        if (empty($this->_optionsArray)) {
            if ($helper->isModuleEnabled('MageWorkshop_DetailedReview')){
                $prosCons = Mage::getModel('detailedreview/review_proscons')->getCollection();
                $prosCons->setType($this->_entityType);
                /** @var MageWorkshop_DetailedReview_Model_Review_Proscons $item */
                foreach ($prosCons as $item) {
                    if ($item->getStatus()) {
                        $this->_optionsArray[] = array(
                            'label' => $item->getName(),
                            'value'=> $item->getEntityId()
                        );
                    }
                }
            } else {
                $this->_optionsArray = array(
                    array(
                        'label' => $helper->__('Price'),
                        'value' => 'price'
                    ),
                    array(
                        'label' => $helper->__('Quality'),
                        'value' => 'quality'
                    ),
                    array(
                        'label' => $helper->__('Manufacturer'),
                        'value' => 'manufacturer'
                    )
                );
            }

        }
        return $this->_optionsArray;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return $this->getAllOptions();
    }
}
