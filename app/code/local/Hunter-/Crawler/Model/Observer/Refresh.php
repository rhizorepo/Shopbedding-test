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

class Hunter_Crawler_Model_Observer_Refresh {
	
    const RESULT_LOG_FILE = 'crawler/result.log';
    const QUEUE_LOG_FILE = 'crawler/queue.log';
    const ERROR_LOG_FILE = 'crawler/error.log';
	
    /**
     * @var int Count of items for cleaning cache per time
     */
    protected $_processingItemsCount = 100;
	
    /**
     * @var array List of entities for cleaning cache by priority
     */
    protected $_pageCachePriorityList = array(
        Mage_Catalog_Model_Category::ENTITY,
        Mage_Catalog_Model_Product::ENTITY,
        'cms_page',
    );
	
    protected $_resource;
    protected $_readConnection;
    protected $_writeConnection;
	
    protected $_lockKey;
	
    public function init() {
        $this->_processingItemsCount = Mage::getStoreConfig('hunter_fpc_crawler/general_settings/processing_items_count');
    }
	
    /**
     * Refresh cache for items from queue
     *
     * @param Varien_Event_Observer $observer
     */
    public function run(Varien_Event_Observer $observer = null) {
        try {
            $beginTime = time();
			
            $this->init();
			
            $errors 	= 0;
            $success 	= 0;
			
			$helper 				= Mage::helper('hunter_crawler');
            $crawlerQueueCollection = $this->_getQueue($observer);
			
			$helper->log('Crawler is running', array(), 1, 'result');
			
            if(count($crawlerQueueCollection) < 1) {
				$helper->log('NOTICE: Queue is empty', array(), 2, 'result');
                return;
            }
			
			$helper->log('Queue size: %s', count($crawlerQueueCollection), 2, 'result');
			
            foreach($crawlerQueueCollection as $queueItem) {
                if($queueItem->getIsLocked() != $this->_lockKey) {
                    continue;
                }
				
                $entity = Mage::getModel('hunter_crawler/factory')->load($queueItem->getEntityType(), $queueItem->getPageKey());
				
				$helper->log('Begin entity id: %s, URL: %s', array($entity->getData('entity_id'), $queueItem->getPageKey()), 3, 'result');
				
                if(!$entity->getUrlPath()) {
					$helper->log(
						'ERROR: The entity "%s" has not been found by URL "%s"',
						array($queueItem->getEntityType(), $queueItem->getPageKey()),
						4,
						'result-error'
					);
					
                    $errors++;
                    continue;
                }
				
                /** Get cache tags */
                $cacheTags 	= $entity->getCacheTags();
                $cacheTag 	= isset($cacheTags[0]) ? $cacheTags[0] : false;
				
                if(!$cacheTag) {
					$helper->log(
						'ERROR: The cache tag of "%s" has not been found for URL "%s"',
						array($queueItem->getEntityType(), $queueItem->getPageKey()),
						4,
						'result-error'
					);
					
                    $errors++;
                    continue;
                }
				
                $cacheTag = $cacheTag . '_' . $entity->getData('entity_id');
				
                /** Clear FPC for current entity */
                $result = Enterprise_PageCache_Model_Cache::getCacheInstance()->clean($cacheTag);
                $result = $result ? '-success-' : '-fail-';
				
				$helper->log('Result of cache cleaning: %s', $result, 4, 'result');
				
				$this->_processEntity($entity);
				
				$categories = array();
				
				if($queueItem->getEntityType() == Mage_Catalog_Model_Category::ENTITY) {
					$categories = $this->_getCategoryList($entity);
				} else if($queueItem->getEntityType() == Mage_Catalog_Model_Product::ENTITY) {
					$category_ids = $entity->getCategoryIds();
					
					$categories = Mage::getResourceModel('catalog/category_collection')
										->addAttributeToSelect('url_path')
										->addFieldToFilter('is_active', 1)
										->addAttributeToFilter('entity_id', array('in' => $category_ids));
				}
				
				if(count($categories)) {
					foreach($categories as $category) {
						$this->_processEntity($category);
					}
				}
				
                $queueItem->delete();
				
                $success++;
            }
			
            if(isset($observer)) {
                $event = $observer->getEvent();
				
                if(isset($event)) {
                    $response = $event->getData('response');
					
                    if($response) {
                        $response->setErrors($errors);
                        $response->setSuccess($success);
                    }
                }
            }
			
            $totalTime = time() - $beginTime;
			
			$helper->log('Job has been finished. Total success count: %s, Total time: %ss', array($success, $totalTime), 2, 'result');
        } catch (Exception $e) {
			$helper->log($e, array(), 1, 'result-error');
        }
    }
	
	protected function _getCategoryList($category) {
		$pathIds = array_reverse(explode(',', $category->getPathInStore()));
		
		foreach($pathIds as $key => $pathId) {
			if(in_array($pathId, array(1, 2, $pathId == $category->getId()))) {
				unset($pathIds[$key]);
			}
		}
		
        $categories = Mage::getResourceModel('catalog/category_collection')
							->setStore(1)
							->addAttributeToSelect('url_key')
							->addAttributeToSelect('url_path')
							->addFieldToFilter('entity_id', array('in' => $pathIds))
							->addFieldToFilter('is_active', 1)
							->load()
							->getItems();
		
		return $categories;
	}
	
	protected function _processEntity($entity) {
		$crawlerResult 	= Mage::getModel('hunter_crawler/result');
		$entityUrl 		= Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB, false) . $entity->getUrlPath();
		
		/* Send first post to entity URL */
		$firstRequestResult = $this->_sendPost($entityUrl);
		
		/* Send second post to entity URL */
		$secondRequestResult = $this->_sendPost($entityUrl);
		
		$crawlerResult->setDate(date('Y-m-d H:i:s', time()));
		$crawlerResult->setPageTitle($entity->getName());
		$crawlerResult->setPageUrl($entityUrl);
		$crawlerResult->setFirstRequest($firstRequestResult);
		$crawlerResult->setSecondRequest($secondRequestResult);
		$crawlerResult->save();
	}
	
    /**
     * Send post
     *
     * @param $entityUrl
     *
     * @return int - time of request
     */
    protected function _sendPost($entityUrl) {
		$options = array(
			CURLOPT_RETURNTRANSFER 	=> true,     // return web page
			CURLOPT_HEADER 			=> true,     // return headers
			CURLOPT_FOLLOWLOCATION 	=> true,     // follow redirects
			CURLOPT_ENCODING 		=> "",       // handle all encodings
			CURLOPT_USERAGENT 		=> "spider", // who am i
			CURLOPT_AUTOREFERER 	=> true,     // set referer on redirect
			CURLOPT_CONNECTTIMEOUT 	=> 10,       // timeout on connect
			CURLOPT_TIMEOUT 		=> 10,       // timeout on response
			CURLOPT_MAXREDIRS 		=> 10,       // stop after 10 redirects
			CURLOPT_SSL_VERIFYPEER 	=> false     // Disabled SSL Cert checks
		);
		
		$helper = Mage::helper('hunter_crawler');
		
        try {
            $ch = curl_init($entityUrl);
			
            curl_setopt_array($ch, $options);
			
            $content 	= curl_exec($ch);
            $err 		= curl_errno($ch);
            $errmsg 	= curl_error($ch);
            $info 		= curl_getinfo($ch);
			
            curl_close($ch);
			
            $info['errno'] 	= $err;
            $info['errmsg'] = $errmsg;
			
            $time 		= isset($info['total_time']) ? $info['total_time'] : -1;
            $httpCode 	= isset($info['http_code']) ? $info['http_code'] : '';
			
			if($httpCode === 404) {
				$helper->log(print_r($info, true), array(), 2, 'result-error-404');
				return false;
			}
			
            if($time < 0 || $httpCode !== 200) {
				$helper->log('Request has been failed', array(), 2, 'result-error');
				$helper->log($info, array(), 2, 'result-error');
            }
        } catch (Exception $e) {
			$helper->log($e->getMessage(), array(), 3, 'result-error');
        }

        return ($time > 0.0001) ? $time : 0;
    }
	
    /**
     * Retrieve queue of URLs
     *
     * @param Varien_Event_Observer $observer
     *
     * @return Hunter_Crawler_Model_Resource_Queue_Collection|Varien_Data_Collection
     */
    protected function _getQueue($observer = null) {
        $crawlerQueueCollection = new Varien_Data_Collection();
		
        try {
            // If manual start
            if(isset($observer)) {
                $event = $observer->getEvent();
				
                if(isset($event)) {
                    $ids = $event->getData('ids');
					
                    if(isset($ids) && is_array($ids)) {
                        $crawlerQueueCollection = Mage::getModel('hunter_crawler/queue')->getCollection()
                            ->addFieldToFilter('entity_id', array('in' => $ids))
                            ->addFieldToFilter('is_locked', Hunter_Crawler_Model_Queue::QUEUE_ITEM_IS_UNLOCKED);
                    }
                }
            }
			
            // If cron start
            if(!isset($observer) || !isset($ids) || !is_array($ids)) {
                /** @var Hunter_Crawler_Helper_Data $helper */
                $helper = Mage::helper('hunter_crawler');
				
                if(!$helper->isLockedCron()) {
                    $totalNumber 			= 0;
                    $arrayIndex 			= 0;
                    $crawlerQueueCollection = new Varien_Data_Collection();
					
                    while($totalNumber < $this->_processingItemsCount && $arrayIndex < count($this->_pageCachePriorityList)) {
                        $limit = $this->_processingItemsCount - $totalNumber;
						
                        /** Chose next entity from priority list */
                        $entityType = $this->_pageCachePriorityList[$arrayIndex++];
						
                        $collection = Mage::getModel('hunter_crawler/queue')->getCollection();
						
                        $collection->addFieldToFilter('entity_type', $entityType);
                        $collection->addFieldToFilter('is_locked', Hunter_Crawler_Model_Queue::QUEUE_ITEM_IS_UNLOCKED);
                        $collection->setPageSize($limit);
                        $collection->setCurPage(1);
						
                        foreach($collection as $item) {
                            $crawlerQueueCollection->addItem($item);
                        }
						
                        $totalNumber = count($crawlerQueueCollection);
                    }
                }
            }
        } catch(Exception $e) {
			Mage::helper('hunter_crawler')->log($e->getMessage(), array(), 4, 'result-error');
        }
		
        $this->_lockKey = $this->_lockPendingQueueItems($crawlerQueueCollection);
		
        return $crawlerQueueCollection;
    }
	
    /**
     * Lock pending queue items
     *
     * @param Varien_Data_Collection $queueCollection
     *
     * @return int
     */
    protected function _lockPendingQueueItems(Varien_Data_Collection $queueCollection) {
        $lockKey = time();
		
        foreach($queueCollection as $queueItem) {
            $queueItem->setIsLocked($lockKey)->save();
        }
		
        return $lockKey;
    }
	
}
