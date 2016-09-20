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

class MageWorkshop_DetailedReview_Model_Review_Sizing extends Varien_Object
{
    const SIZING_RUNS_SMALL       = 1;
    const SIZING_SNUG             = 2;
    const SIZING_LITTLE_BIT_TIGHT = 3;
    const SIZING_TRUE_TO_SIZE     = 4;
    const SIZING_LITTLE_BIT_LOOSE = 5;
    const SIZING_ROOMY            = 6;
    const SIZING_RUNS_LARGE       = 7;

    const STANDARD_THEME_COEF = 7;
    const BEIGE_THEME_COEF    = 9;

    /**
     * @return array
     */
    static public function getOptionArray()
    {
        $helper = Mage::helper('detailedreview');
        return array(
            self::SIZING_RUNS_SMALL       => $helper->__('runs small'),
            self::SIZING_SNUG             => $helper->__('snug'),
            self::SIZING_LITTLE_BIT_TIGHT => $helper->__('little bit tight'),
            self::SIZING_TRUE_TO_SIZE     => $helper->__('true to size'),
            self::SIZING_LITTLE_BIT_LOOSE => $helper->__('little bit loose'),
            self::SIZING_ROOMY            => $helper->__('roomy'),
            self::SIZING_RUNS_LARGE       => $helper->__('runs large')
        );
    }

    /**
     * @return string
     */
    static public function getIndent($item)
    {
        $sizing = array(
            self::SIZING_RUNS_SMALL       => '31px',
            self::SIZING_SNUG             => '16px',
            self::SIZING_LITTLE_BIT_TIGHT => '35px',
            self::SIZING_TRUE_TO_SIZE     => '32px',
            self::SIZING_LITTLE_BIT_LOOSE => '38px',
            self::SIZING_ROOMY            => '20px',
            self::SIZING_RUNS_LARGE       => '32px',
        );
        return $sizing[$item];
    }
    /**
     * @param int $key
     * @return mixed
     */
    public function getOptionValue($key)
    {
        $options = $this->getOptionArray();
        return array_key_exists((int) $key, $options) ? $options[$key] : $options[$this->getDefaultSizing()];
    }

    /**
     * @param int $sizing
     * @return int
     */
    public function getOptionWidth($sizing)
    {
        $coef = MageWorkshop_DetailedReview_Model_Review_Sizing::STANDARD_THEME_COEF;

        $options = $this->getOptionArray();
        if (array_key_exists((int) $sizing, $options)) {
            return $coef + (100 - $coef) / ($this->count() - 1) * ($sizing - 1);
        }
        return $this->getOptionWidth($this->getDefaultSizing());
    }

    /**
     * @return int
     */
    static public function getDefaultSizing()
    {
        return self::SIZING_TRUE_TO_SIZE;
    }

    /**
     * @return int
     */
    public function count()
    {
        return count($this->getOptionArray());
    }
}
