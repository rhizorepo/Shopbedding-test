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
class Mage_Detailedreview_Model_Source_EntityType
{
    const CONS = 'C';
    const PROS = 'P';
    const ALL = 'A';

    /**
     * @return array
     */
    public static function toShortOptionArray()
    {
        $helper = Mage::helper('core');
        return array(
            self::PROS => $helper->__('Pros'),
            self::CONS => $helper->__('Cons'),
            self::ALL  => $helper->__('User-defined Pros and Cons')
        );
    }

    /**
     * @return array
     */
    public static function toShortOptionPCOnlyArray()
    {
        $helper = Mage::helper('core');
        return array(
            self::PROS => $helper->__('Pros'),
            self::CONS => $helper->__('Cons')
        );
    }

    /**
     * @param string $type
     * @return bool|string
     */
    public static function getEntityNameByType($type)
    {
        if ($type != null) {
            $entities = self::toShortOptionArray();
            return $entities[$type];
        }
        return false;
    }

    /**
     * @param $type
     * @return bool|string
     */
    public static function getClassNameByType($type)
    {
        if ($type) {
            $entities = array(
                self::PROS => 'pros',
                self::CONS => 'cons',
                self::ALL  => 'userproscons'
            );
            return $entities[$type];
        }
        return false;
    }
}
