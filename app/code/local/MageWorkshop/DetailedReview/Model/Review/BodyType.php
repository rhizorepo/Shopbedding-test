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
class MageWorkshop_DetailedReview_Model_Review_BodyType
{
    protected $_options;

    const BODY_TYPE_TRIANGLE    = 1;
    const BODY_TYPE_PEAR        = 2;
    const BODY_TYPE_RECTANGLE   = 3;
    const BODY_TYPE_HOURGLASS   = 4;
    const BODY_TYPE_DIAMOND     = 5;


    /**
     * @return array
     */
    public function getOptionArray()
    {
        return array(
            self::BODY_TYPE_TRIANGLE    => 'triangle',
            self::BODY_TYPE_PEAR        => 'pear',
            self::BODY_TYPE_RECTANGLE   => 'rectangle',
            self::BODY_TYPE_HOURGLASS   => 'hourglass',
            self::BODY_TYPE_DIAMOND     => 'diamond',
        );
    }

    /**
     * @param bool $isMultiSelect
     * @return array
     */
    public function toOptionArray($isMultiSelect = false)
    {
        if (!$this->_options) {
            $this->_options = $this->getOptionArray();
        }

        $options = $this->_options;
        if (!$isMultiSelect) {
            array_unshift($options, Mage::helper('adminhtml')->__('-- Please Select --'));
        }

        return $options;
    }

    /**
     * @param int $key
     * @return mixed
     */
    public function getOptionValue($key)
    {
        $options = $this->getOptionArray();
        return array_key_exists($key, $options) ? $options[$key] : $options[self::BODY_TYPE_PEAR];
    }
}
