<?php
/**
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the MIT License
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/mit-license.php
 * 
 * @category    Hunter
 * @package     Hunter_Crawler
 * @copyright   Copyright (c) 2015
 * @license     http://opensource.org/licenses/mit-license.php MIT License
 * @author      Roman Tkachenko roman.tkachenko@huntersconsult.com
 */ 
class Hunter_Crawler_Model_Queue extends Mage_Core_Model_Abstract
{
    const QUEUE_ITEM_IS_LOCKED = 1;
    const QUEUE_ITEM_IS_UNLOCKED = 0;

    const QUEUE_ITEM_LOCK_STATUS_MESSAGE = 'locked';

    protected function _construct()
    {
        $this->_init('hunter_crawler/queue');
    }

    public function isLocked()
    {
        return $this->getIsLocked() != self::QUEUE_ITEM_IS_UNLOCKED;
    }

}