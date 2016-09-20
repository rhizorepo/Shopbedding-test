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
class Hunter_Crawler_Model_Resource_Queue_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{

    protected function _construct()
    {
        $this->_init('hunter_crawler/queue');
    }

    public function lockAllItems()
    {
        $this->_updateIsLockedField(Hunter_Crawler_Model_Queue::QUEUE_ITEM_IS_LOCKED);
    }

    public function unlockAllItems()
    {
        $this->_updateIsLockedField(Hunter_Crawler_Model_Queue::QUEUE_ITEM_IS_UNLOCKED);
    }

    protected function _updateIsLockedField($isLocked)
    {
        $resource = Mage::getSingleton('core/resource');
        $writeConnection = $resource->getConnection('core_write');

        $tableName = $resource->getTableName('hunter_crawler/crawler_queue');

        $query = <<<SQL
        UPDATE $tableName SET is_locked = $isLocked
SQL;

        $writeConnection->query($query);
    }
}