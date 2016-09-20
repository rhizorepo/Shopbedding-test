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
abstract class Mage_Detailedreview_Model_Category_Attribute_Backend_ProsConsAbstract
    extends Mage_Eav_Model_Entity_Attribute_Backend_Abstract
{
    protected $_entityType;
    protected $_attributeName;

    public function __construct()
    {
        $this->_attributeName = Mage_Detailedreview_Model_Source_EntityType::getClassNameByType($this->_entityType);
    }

    /**
     * Before Attribute Save Process
     *
     * @param Varien_Object $object
     * @return $this
     */
    public function beforeSave($object)
    {
        $attributeCode = $this->getAttribute()->getName();
        if ($attributeCode == $this->_attributeName) {
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
     * @return $this
     */
    public function afterLoad($object)
    {
        $attributeCode = $this->getAttribute()->getName();
        if ($attributeCode == $this->_attributeName) {
            $data = $object->getData($attributeCode);
            if ($data) {
                $object->setData($attributeCode, explode(',', $data));
            }
        }

        return $this;
    }
}
