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
class MageWorkshop_DetailedReview_Model_Mysql4_Review_Reports_Activity
{
    /**
     * @return Zend_Db_Adapter_Abstract
     */
    public function getConnection()
    {
        return Mage::getSingleton('core/resource')->getConnection('core_read');
    }

    /**
     * @param string $range
     * @param int $customStart
     * @param int $customEnd
     * @return Varien_Data_Collection
     */
    public function getActivity($range, $customStart = 0, $customEnd = 0)
    {
        $connection = $this->getConnection();
        $select = new Zend_Db_Select($connection);

        $dateRange = $this->getDateRange($range, $customStart, $customEnd);
        $tzRangeOffsetExpression = $this->_getRangeExpressionForAttribute($range, 'created_at');

        $from = new Zend_Date($dateRange['from']);
        $from = $from->toString($this->getTimeFormat($range));

        $to = new Zend_Date($dateRange['to']);
        $to = $to->toString($this->getTimeFormat($range));

        $select->from(Mage::getSingleton('core/resource')->getTableName('review'), array(
                'quantity' => 'COUNT(entity_id)',
                'range' => $tzRangeOffsetExpression,
            ))
            ->where('created_at > ?', $from )
            ->where('created_at < ?', $to )
            ->order('range', Zend_Db_Select::SQL_ASC)
            ->group($tzRangeOffsetExpression);

        $collection = new Varien_Data_Collection();
        foreach ($connection->fetchAll($select) as $result) {
            $item = new Varien_Object(array(
                'quantity' => $result['quantity'],
                'range' => $result['range']
            ));
            $collection->addItem($item);
        }
        Mage::dispatchEvent('detailedreview_review_reports_activity', array(
            'collection' => $collection
        ));
        return $collection;
    }

    /**
     * @param string $period
     * @return string
     */
    protected function getTimeFormat($period)
    {
        switch ($period) {
            case '7d':
            case '1m':
                $format = 'yyyy-MM-dd';
                break;
            case '1y':
            case '2y':
                $format = 'yyyy-MM';
                break;
            default: // '24h'
                $format = 'yyyy-MM-dd HH:00';
        }
        return $format;
    }

    /**
     * Calculate From and To dates (or times) by given period
     *
     * @param string $range
     * @param string $customStart
     * @param string $customEnd
     * @param boolean $returnObjects
     * @return array
     */
    public function getDateRange($range, $customStart, $customEnd, $returnObjects = false)
    {
        $dateEnd = Mage::app()->getLocale()->date();
        $dateStart = clone $dateEnd;

        // go to the end of a day
        $dateEnd->setHour(23);
        $dateEnd->setMinute(59);
        $dateEnd->setSecond(59);

        $dateStart->setHour(0);
        $dateStart->setMinute(0);
        $dateStart->setSecond(0);

        switch ($range)
        {
            case '7d':
                // subtract 6 days we need to include
                // only today and not hte last one from range
                $dateStart->subDay(6);
                break;
            case '1m':
                $dateStart->setDay(Mage::getStoreConfig('reports/dashboard/mtd_start'));
                break;
            case 'custom':
                $dateStart = $customStart ? $customStart : $dateEnd;
                $dateEnd = $customEnd ? $customEnd : $dateEnd;
                break;
            case '1y':
            case '2y':
                $startMonthDay = explode(',', Mage::getStoreConfig('reports/dashboard/ytd_start'));
                $startMonth = isset($startMonthDay[0]) ? (int)$startMonthDay[0] : 1;
                $startDay = isset($startMonthDay[1]) ? (int)$startMonthDay[1] : 1;
                $dateStart->setMonth($startMonth);
                $dateStart->setDay($startDay);
                $dateStart->subYear(1);
                break;
            default: // '24h'
                $dateEnd = Mage::app()->getLocale()->date();
                $dateEnd->addHour(1);
                $dateStart = clone $dateEnd;
                $dateStart->subDay(1);
                break;
        }

        $dateStart->setTimezone('Etc/UTC');
        $dateEnd->setTimezone('Etc/UTC');

        if ($returnObjects) {
            return array($dateStart, $dateEnd);
        } else {
            return array('from' => $dateStart, 'to' => $dateEnd, 'datetime' => true);
        }
    }

    /**
     * Get range expression
     *
     * @param string $range
     * @return Zend_Db_Expr
     */
    protected function _getRangeExpression($range)
    {
        switch ($range) {
            case '24h':
                $expression = 'DATE_FORMAT({{attribute}}, \'%Y-%m-%d %H:00\')';
                break;
            case '7d':
            case '1m':
                $expression = 'DATE_FORMAT({{attribute}}, \'%Y-%m-%d\')';
                break;
            case '1y':
            case '2y':
            case 'custom':
            default:
                $expression = 'DATE_FORMAT({{attribute}}, \'%Y-%m\')';
                break;
        }
        return $expression;
    }

    /**
     * Retrieve range expression adapted for attribute
     *
     * @param string $range
     * @param string $attribute
     * @return string
     */
    protected function _getRangeExpressionForAttribute($range, $attribute)
    {
        $expression = $this->_getRangeExpression($range);
        return str_replace('{{attribute}}', $this->getConnection()->quoteIdentifier($attribute), $expression);
    }
}
