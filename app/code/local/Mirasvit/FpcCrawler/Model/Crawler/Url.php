<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at http://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   Full Page Cache
 * @version   1.0.12
 * @build     587
 * @copyright Copyright (C) 2016 Mirasvit (http://mirasvit.com/)
 */



class Mirasvit_FpcCrawler_Model_Crawler_Url extends Mage_Core_Model_Abstract
{
     protected function _construct()
    {
        $this->_init('fpccrawler/crawler_url');
    }

    public function saveUrl($line, $rate = 1)
    {
        if (count($line) < 9) {
            return $this;
        }

        $url = $line[2];
        $cacheId = preg_replace('/\s+/', ' ', trim($line[3]));
        $storeId = trim($line[7]);
        $currency = trim($line[8]);
        $mobileGroup = trim($line[9]);

        $collection = $this->getCollection();
        $collection->getSelect()->where('url = ?', $url)
                                ->where('store_id = ?', $storeId)
                                ->where('currency = ?', $currency)
                                ->where('mobile_group = ?', $mobileGroup)
                                ;
        $model = $collection->getFirstItem();

        try {
            if (trim($cacheId) != '') {
                $model->setCacheId($cacheId)
                        ->setUrl($url)
                        ->setRate(intval($model->getRate()) +  $rate)
                        ->setSortByPageType(trim($line[4]))
                        ->setStoreId($storeId)
                        ->setCurrency($currency)
                        ->setMobileGroup($mobileGroup)
                        ;
                if (isset($line[5])) {
                    $model->setSortByProductAttribute(trim($line[5]))
                        ->save();
                } else {
                    $model->save();
                }
            } elseif ($model->getId()) {
                $model->setRate(intval($model->getRate()) +  $rate)
                    ->save();
            }
        } catch (Exception $e) {
        }

        return $this;
    }

    public function isCacheExist()
    {
        $cache = Mirasvit_Fpc_Model_Cache::getCacheInstance();
        $cacheId = $this->getCacheId();

        if (is_string($cacheId) && $cache->load($cacheId)) {
            return true;
        }

        return false;
    }

    public function clearCache()
    {
        $cache = Mirasvit_Fpc_Model_Cache::getCacheInstance();
        $cache->remove($this->getCacheId());

        return $this;
    }

    public function warmCache()
    {
        $url = $this->getUrl();
        $userAgent = Mage::helper('fpccrawler')->getUserAgent(null, $this->getStoreId(), $this->getCurrency());
        $content = '';
        if (function_exists('curl_multi_init')) {
            $adapter = new Varien_Http_Adapter_Curl();
            $options = array(
                CURLOPT_USERAGENT => $userAgent,
                CURLOPT_HEADER => true,
            );

            $content = $adapter->multiRequest(array($url), $options);
            $content = $content[0];
        } else {
            ini_set('user_agent', $userAgent);
            $content = implode(PHP_EOL, get_headers($url));
        }

        if (strpos($content, '404 Not Found') !== false
            || strpos($content, '301 Moved Permanently') !== false
            || strpos($content, 'HTTP/1.1 302 Found') !== false) {
                $this->delete();
        }

        preg_match('/Fpc-Cache-Id: ('.Mirasvit_Fpc_Model_Config::REQUEST_ID_PREFIX.'[a-z0-9]{32})/', $content, $matches);
        if (count($matches) == 2) {
            $cacheId = $matches[1];
            if ($this->getCacheId() != $cacheId) {
                $this->setCacheId($cacheId)
                    ->save();
            }
        } else {
            $this->delete();
        }

        return $this;
    }
}
