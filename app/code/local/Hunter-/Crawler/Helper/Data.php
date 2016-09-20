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
class Hunter_Crawler_Helper_Data extends Mage_Core_Helper_Abstract
{
    public $RESULT_LOG_FILE = 'crawler/result.log';
    public $QUEUE_LOG_FILE = 'crawler/queue.log';
    public $errorLogFile = 'crawler/error.log';

    /**
     * @var array List of entities for cleaning cache by priority
     */
    public $pageCachePriorityList = array(
        Mage_Catalog_Model_Category::ENTITY,
        Mage_Catalog_Model_Product::ENTITY,
        'cms_page',
    );

    /**
     * Check if previous cron crawler has been finished
     *
     * @return bool
     */
    public function isLockedCron()
    {
        return Mage::getStoreConfig('hunter_fpc_crawler/general_settings/cron_lock');
    }

    /**
     * Lock cron job
     */
    public function lockCron()
    {
        Mage::getConfig()->saveConfig('hunter_fpc_crawler/general_settings/cron_lock', true);
        Mage::app()->reinitStores();
        Mage::getConfig()->removeCache();
    }

    /**
     * Unlock cron job
     */
    public function unlockCron()
    {
        Mage::getConfig()->saveConfig('hunter_fpc_crawler/general_settings/cron_lock', false);
        Mage::app()->reinitStores();
        Mage::getConfig()->removeCache();
    }
	
	public function log($message, $data = array(), $level = 1, $file = 'cron') {
		$message = is_array($message) ? print_r($message, true) : $message;
		
		Mage::log(
				vsprintf(
					'%s %s',
					array(
						str_repeat('-', ((((int) $level) > 0 ? (int) $level : 1) * 5)),
						vsprintf($message, is_array($data) ? $data : array($data))
					)
				),
				null,
				sprintf('crawler-%s.log', ($file ? $file : 'cron')),
				true
			);
	}
	
}
