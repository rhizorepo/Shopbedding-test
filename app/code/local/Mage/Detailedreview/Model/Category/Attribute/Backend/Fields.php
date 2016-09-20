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
class Mage_Detailedreview_Model_Category_Attribute_Backend_Fields
    extends Mage_Eav_Model_Entity_Attribute_Backend_Abstract
{
    /**
     * Before Attribute Save Process
     *
     * @param Varien_Object $object
     * @return $this
     */
    public function beforeSave($object)
    {
        $attributeCode = $this->getAttribute()->getName();
        if ($attributeCode == 'review_fields_available') {
            $data = $object->getData($attributeCode);
            if (!is_array($data)) {
                $data = array();
            }
            $object->setData($attributeCode, join(',', $data));
        }
        if (is_null($object->getData($attributeCode))) {
            $object->setData($attributeCode, false);
        }
        return $this;
    }

    /**
     * @param Varien_Object $object
     * @return $this|Mage_Eav_Model_Entity_Attribute_Backend_Abstract
     */
    public function afterLoad($object)
    {
        $attributeCode = $this->getAttribute()->getName();
        if ($attributeCode == 'review_fields_available') {
            $data = $object->getData($attributeCode);
            if ($data) {
                $object->setData($attributeCode, explode(',', $data));
            }
        }

        return $this;
    }

    /**
     * @param Varien_Object $object
     * @return bool
     * @throw Exception
     */
    public function validate($object)
    {
        /** @var Mage_Eav_Model_Entity_Attribute_Abstract $attribute */
        $attribute = $this->getAttribute();
        $attributeCode = $attribute->getName();
        $postDataConfig = ($object->hasData('use_post_data_config')) ? (array) $object->getData('use_post_data_config') : array();

        $isUseConfig = false;
        if ($postDataConfig) {
            $isUseConfig = in_array($attributeCode, $postDataConfig);
        }

        if ($attribute->getIsRequired()) {
            $attributeValue = $object->getData($attributeCode);
            if ($attribute->isValueEmpty($attributeValue)) {
                if (is_array($attributeValue) && count($attributeValue) > 0) {
                } elseif (!$isUseConfig) {
                    return false;
                }
            }
        }

        if ($attributeCode == 'review_fields_available') {
            if ($available = $object->getData('review_fields_available')) {
                return (bool) is_array($object->getReviewFieldsAvailable());
            }
        }
        return true;
    }
}
